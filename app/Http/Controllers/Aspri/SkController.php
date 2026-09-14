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
use App\Services\ActivityLogger;
use App\Models\NotificationHistory;

class SkController extends Controller
{
    protected FcmService $fcmService;
    protected WhatsAppNotificationService $waService;

    public function __construct(FcmService $fcmService, WhatsAppNotificationService $waService)
    {
        $this->fcmService = $fcmService;
        $this->waService = $waService;
    }

    public function index()
    {
        $petinggiList = User::where('role', 'petinggi')->get();
        $dirutList = User::where('role', 'dirut')->get();

        $documents = Document::with(['latestVersion', 'approvals.user', 'comments'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DITINJAU', 'PENDING', 'DITINJAU_DIREKSI', 'PENDING_DIREKSI', 'SIAP_TTD', 'DISETUJUI_TANPA_TTD'])
            ->latest()
            ->get();

        return view('app.aspri.dashboard', compact('petinggiList', 'dirutList', 'documents'));
    }

    public function detail($id)
    {
        $document = Document::with(['latestVersion', 'approvals.user', 'comments.user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

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
            ->whereIn('status', ['DITINJAU', 'PENDING', 'DITINJAU_DIREKSI', 'PENDING_DIREKSI', 'SIAP_TTD'])
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

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document_number' => 'required|string|unique:documents,document_number',
            'pdf_file' => 'required|mimes:pdf|max:10240',
            'petinggi_ids' => 'required|array',
            'perlu_direksi' => 'nullable|boolean',
            'direksi_ids' => 'required_if:perlu_direksi,1|nullable|array',
            'direksi_ids.*' => 'exists:users,id',
        ]);

        $needDireksi = $request->boolean('perlu_direksi');

        $document = DB::transaction(function () use ($request, $needDireksi) {
            $file = $request->file('pdf_file');

            $safeTitle = Str::slug($request->title);
            $fileName = $safeTitle . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents/v1', $fileName, 'public');

            $document = Document::create([
                'user_id' => Auth::id() ?? 1,
                'title' => $request->title,
                'document_number' => $request->document_number,
                'status' => 'DITINJAU',
                'need_direksi' => $needDireksi,
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
                    'role' => 'validator',
                    'status' => 'MENUNGGU',
                    'need_sign' => $request->input("need_sign.$petinggiId", 0),
                ]);
            }

            if ($needDireksi) {
                foreach ($request->direksi_ids as $direksiId) {
                    DocumentApproval::create([
                        'document_id' => $document->id,
                        'user_id' => $direksiId,
                        'role' => 'direksi',
                        'status' => 'MENUNGGU',
                        'need_sign' => $request->input("need_sign_direksi.$direksiId", 0),
                    ]);
                }
            }

            $validatorNames = $document->approvals()
                ->where('role', 'validator')
                ->with('user')
                ->get()
                ->pluck('user.name')
                ->join(', ');

            ActivityLogger::log(
                action: 'kirim_sk',
                description: Auth::user()->name . " (Admin) mengirim SK \"{$document->title}\" kepada Validator: {$validatorNames}",
                documentId: $document->id,
                meta: ['validators' => $document->approvals()->where('role', 'validator')->pluck('user_id')]
            );

            return $document;
        });

        $document->load('approvals.user');
        foreach ($document->approvals->where('role', 'validator') as $approval) {
            $this->waService->sendWaPengajuanBaru($approval->user, $document);

            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Pengajuan SK Baru',
                    "Ada pengajuan SK baru dari Administrator: \"{$document->title}\" perlu Anda tinjau.",
                    [
                        'document_id' => (string) $document->id,
                        'type' => 'pengajuan_baru',
                        'url' => route('app.petinggi.dashboard', ['doc' => $document->id]),
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

    public function uploadRevision(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $document = Document::with('versions', 'approvals.user')->findOrFail($id);

        if (!in_array($document->status, ['PENDING', 'PENDING_DIREKSI'])) {
            return redirect()->back()->with('error', 'Dokumen ini tidak sedang dalam status revisi.');
        }

        $fromDireksi = $document->status === 'PENDING_DIREKSI';

        DB::transaction(function () use ($request, $document, $fromDireksi) {
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

            if ($fromDireksi) {
                $document->approvals()->where('role', 'direksi')->update(['status' => 'MENUNGGU']);
                $newStatus = 'DITINJAU_DIREKSI';
            } else {
                $document->approvals()->update(['status' => 'MENUNGGU']);
                $newStatus = 'DITINJAU';
            }

            $document->update([
                'status' => $newStatus,
                'title' => $request->title,
            ]);
        });

        $notifyRole = $fromDireksi ? 'direksi' : 'validator';
        $notifUrl = $fromDireksi
            ? route('app.dirut.dashboard', ['doc' => $document->id])
            : route('app.petinggi.dashboard', ['doc' => $document->id]);

        foreach ($document->approvals->where('role', $notifyRole) as $approval) {
            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Revisi SK Diunggah',
                    "Administrator telah mengunggah revisi dokumen \"{$document->title}\", perlu ditinjau ulang.",
                    [
                        'document_id' => (string) $document->id,
                        'type' => 'revisi_diunggah',
                        'url' => $notifUrl,
                    ]
                );
            }
        }

        $successMsg = $fromDireksi
            ? 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Direksi.'
            : 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Validator.';

        return redirect()->back()->with('success', $successMsg);
    }

    public function showTtdEditor($id)
    {
        $document = Document::with('latestVersion')
            ->where('user_id', Auth::id())
            ->where('status', 'DISETUJUI_TANPA_TTD')
            ->findOrFail($id);

        $dirutAlreadySigned = !is_null($document->signed_at);

        $signedUserIds = $document->signaturePlacements()->pluck('user_id')->toArray();

        $pendingDireksi = $document->approvals()
            ->where('role', 'direksi')
            ->where('status', 'TERIMA')
            ->where('need_sign', true)
            ->whereNotIn('user_id', $signedUserIds)
            ->with('user')
            ->get();

        if (!$dirutAlreadySigned && $pendingDireksi->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada Direksi dengan tanda tangan tertunda untuk dokumen ini.');
        }

        $pendingValidators = $document->approvals()
            ->where('role', 'validator')
            ->where('status', 'TERIMA')
            ->where('need_sign', true)
            ->whereNotIn('user_id', $signedUserIds)
            ->with('user')
            ->get();

        $pdfSourcePath = $dirutAlreadySigned ? $document->signed_file_path : $document->latestVersion->file_path;

        return view('app.aspri.ttd-editor', compact('document', 'pendingDireksi', 'pendingValidators', 'dirutAlreadySigned', 'pdfSourcePath'));
    }

    public function saveTtd(Request $request, $id)
    {
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf',
            'qr_hash' => 'nullable|string',
        ]);

        $doc = Document::with('latestVersion')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($doc->status !== 'DISETUJUI_TANPA_TTD') {
            return redirect()->back()->with('error', 'Dokumen ini tidak sedang menunggu penempelan TTD.');
        }

        $signedFile = $request->file('signed_pdf');
        $safeDocNumber = Str::slug($doc->document_number);
        $signedFileName = 'signed_' . $safeDocNumber . '_' . time() . '.pdf';
        $signedRelativePath = 'documents/signed/' . $signedFileName;
        Storage::disk('public')->put($signedRelativePath, file_get_contents($signedFile));

        $fullPath = storage_path('app/public/' . $signedRelativePath);
        $fileHash = hash_file('sha256', $fullPath);

        $doc->update([
            'status' => 'DISETUJUI',
            'qr_code_hash' => $doc->qr_code_hash ?? $request->qr_hash,
            'signed_at' => $doc->signed_at ?? now(),
            'signed_file_path' => $signedRelativePath,
            'file_hash' => $fileHash,
        ]);

        NotificationHistory::where('data->document_id', (string) $doc->id)->delete();

        return redirect()->route('app.aspri.dashboard')->with('success', 'Tanda tangan berhasil ditempel, dokumen kini sah sepenuhnya!');
    }
}