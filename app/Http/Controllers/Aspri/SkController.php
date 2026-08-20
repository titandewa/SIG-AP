<?php

namespace App\Http\Controllers\Aspri;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Services\FcmService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SkController extends Controller
{
    protected FcmService $fcmService;
    protected WhatsAppNotificationService $waService;

    public function __construct(FcmService $fcmService, WhatsAppNotificationService $waService)
    {
        $this->fcmService = $fcmService;
        $this->waService = $waService;
    }

    // Dashboard Administrator Real
    public function index()
    {
        $petinggiList = User::where('role', 'petinggi')->get();

        $documents = Document::with(['latestVersion', 'approvals.user', 'comments'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DITINJAU', 'PENDING', 'SIAP_TTD', 'DISETUJUI_TANPA_TTD'])
            ->latest()
            ->get();

        return view('app.aspri.dashboard', compact('petinggiList', 'documents'));
    }

    public function detail($id)
    {
        $document = Document::with(['latestVersion', 'approvals.user', 'comments.user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Tandai semua komentar di dokumen ini sebagai sudah dibaca
        $document->comments()->update(['is_read' => true]);

        return view('app.aspri.detail', compact('document'));
    }

    public function destroy($id)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($id);

        if ($document->status === 'DISETUJUI') {
            return redirect()->back()->with('error', 'Dokumen yang sudah disetujui tidak bisa dihapus.');
        }

        DB::transaction(function () use ($document) {
            foreach ($document->versions as $version) {
                Storage::disk('public')->delete($version->file_path);
            }
            $document->comments()->delete();
            $document->approvals()->delete();
            $document->versions()->delete();
            $document->delete();
        });

        return redirect()->route('app.aspri.dashboard')->with('success', 'Pengajuan SK berhasil dihapus.');
    }

    public function listAktif()
    {
        $documents = Document::with(['latestVersion', 'approvals.user'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DITINJAU', 'PENDING', 'SIAP_TTD'])
            ->latest()
            ->get();

        return view('app.aspri.list-aktif', compact('documents'));
    }

    public function riwayat(Request $request)
    {
        $query = Document::with(['latestVersion', 'approvals.user'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DISETUJUI', 'DITOLAK']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('updated_at', 'desc')->get();

        return view('app.aspri.riwayat', compact('documents'));
    }

    // Simpan Pengajuan SK Baru (Oleh Administrator)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_number' => 'required|string|unique:documents,document_number',
            'pdf_file' => 'required|mimes:pdf|max:10240',
            'petinggi_ids' => 'required|array',
        ]);

        $document = DB::transaction(function () use ($request) {
            $file = $request->file('pdf_file');

            $safeTitle = Str::slug($request->title);
            $fileName = $safeTitle . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents/v1', $fileName, 'public');

            $document = Document::create([
                'user_id' => Auth::id() ?? 1,
                'title' => $request->title,
                'document_number' => $request->document_number,
                'status' => 'DITINJAU',
            ]);

            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => 1,
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            foreach ($request->petinggi_ids as $petinggiId) {
                DocumentApproval::create([
                    'document_id' => $document->id,
                    'user_id' => $petinggiId,
                    'status' => 'MENUNGGU',
                    'need_sign' => $request->input("need_sign.$petinggiId", 0),
                ]);
            }

            return $document;
        });

        // Kirim WA + FCM notif ke semua Validator yang jadi reviewer
        $document->load('approvals.user');
        foreach ($document->approvals as $approval) {
            $this->waService->sendWaPengajuanBaru($approval->user, $document);

            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Pengajuan SK Baru',
                    "Ada pengajuan SK baru dari Administrator: \"{$document->title}\" perlu Anda tinjau.",
                    [
                        'document_id' => (string) $document->id,
                        'type' => 'pengajuan_baru',
                        'url' => route('app.petinggi.dashboard'),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'SK berhasil diajukan dan diteruskan ke para Validator!');
    }

    public function versionHistory($id)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($id);
        $versions = $document->versions()->orderBy('version_number', 'desc')->get();

        return view('app.aspri.version-history', compact('document', 'versions'));
    }

    // Logika Re-Upload
    public function uploadRevision(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $document = Document::with('versions', 'approvals.user')->findOrFail($id);

        if ($document->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Dokumen ini tidak sedang dalam status revisi.');
        }

        DB::transaction(function () use ($request, $document) {
            $file = $request->file('pdf_file');

            $safeTitle = Str::slug($request->title);
            $customFileName = $safeTitle . '_' . time() . '.pdf';
            $filePath = $file->storeAs('documents/v1', $customFileName, 'public');

            $nextVersion = $document->versions()->max('version_number') + 1;

            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersion,
                'file_path' => $filePath,
                'original_filename' => $request->title . '.pdf',
            ]);

            // Reset semua approval Validator karena dokumen berubah
            $document->approvals()->update(['status' => 'MENUNGGU']);

            $document->update([
                'status' => 'DITINJAU',
                'title' => $request->title,
            ]);
        });

        // Kirim FCM ke semua Validator reviewer bahwa ada revisi baru untuk ditinjau ulang
        foreach ($document->approvals as $approval) {
            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Revisi SK Diunggah',
                    "Administrator telah mengunggah revisi dokumen \"{$document->title}\", perlu ditinjau ulang.",
                    ['document_id' => (string) $document->id, 'type' => 'revisi_diunggah']
                );
            }
        }

        return redirect()->back()->with('success', 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Validator.');
    }

    public function showTtdEditor($id)
    {
        $document = Document::with('latestVersion')
            ->where('user_id', Auth::id())
            ->where('status', 'DISETUJUI_TANPA_TTD')
            ->findOrFail($id);

        $dirut = User::where('role', 'dirut')->whereNotNull('signature_path')->first();

        if (!$dirut) {
            return redirect()->back()->with('error', 'Belum ada akun Direksi yang mengatur tanda tangan.');
        }

        // Cari petinggi yang statusnya sudah TERIMA, butuh TTD (need_sign),
        // tapi belum ada tanda tangannya (SignaturePlacement) untuk dokumen ini
        $signedUserIds = $document->signaturePlacements()->pluck('user_id')->toArray();

        $pendingValidators = $document->approvals()
            ->where('status', 'TERIMA')
            ->where('need_sign', true)
            ->whereNotIn('user_id', $signedUserIds)
            ->with('user')
            ->get();

        return view('app.aspri.ttd-editor', compact('document', 'dirut', 'pendingValidators'));
    }

    public function saveTtd(Request $request, $id)
    {
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf',
            'qr_hash' => 'required|string',
        ]);

        $doc = Document::with('latestVersion')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($doc->status !== 'DISETUJUI_TANPA_TTD') {
            return redirect()->back()->with('error', 'Dokumen ini tidak sedang menunggu penempelan TTD.');
        }

        $signedFile = $request->file('signed_pdf');
        $signedFileName = 'signed_' . $doc->document_number . '_' . time() . '.pdf';
        $signedRelativePath = 'documents/signed/' . $signedFileName;
        Storage::disk('public')->put($signedRelativePath, file_get_contents($signedFile));

        $fullPath = storage_path('app/public/' . $signedRelativePath);
        $fileHash = hash_file('sha256', $fullPath);

        $doc->update([
            'status' => 'DISETUJUI',
            'qr_code_hash' => $request->qr_hash,
            'signed_at' => now(),
            'signed_file_path' => $signedRelativePath,
            'file_hash' => $fileHash,
        ]);

        return redirect()->route('app.aspri.dashboard')->with('success', 'Tanda tangan berhasil ditempel, dokumen kini sah sepenuhnya!');
    }
}