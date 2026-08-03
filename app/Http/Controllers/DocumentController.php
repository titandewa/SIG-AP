<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentVersion;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use setasign\Fpdi\Fpdi;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class DocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1. ROUTE / WORKSPACE DEMO (Bahan Presentasi Atasan)
    |--------------------------------------------------------------------------
    */

    public function indexDemoAspri()
    {
        $petinggiList = User::where('role', 'petinggi')->get();
        $documents = Document::with(['versions', 'approvals.user', 'latestVersion'])->latest()->get();

        return view('demo.aspri', compact('petinggiList', 'documents'));
    }

    public function verifyDocument($hash)
    {
        $document = Document::with('user')->where('qr_code_hash', $hash)->first();

        return view('verify', compact('document'));
    }


    /*
    |--------------------------------------------------------------------------
    | 2. ROUTE / WORKSPACE REAL APP (Sistem Eksperimen Real)
    |--------------------------------------------------------------------------
    */

    // Dashboard Aspri Real
    public function indexAspri()
    {
        $petinggiList = User::where('role', 'petinggi')->get();

        $documents = Document::with(['latestVersion', 'approvals.user', 'comments'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DITINJAU', 'PENDING', 'SIAP_TTD'])
            ->latest()
            ->get();

        return view('app.aspri.dashboard', compact('petinggiList', 'documents'));
    }

    public function detailAspri($id)
    {
        $document = Document::with(['latestVersion', 'approvals.user', 'comments.user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Tandai semua komentar di dokumen ini sebagai sudah dibaca
        $document->comments()->update(['is_read' => true]);

        return view('app.aspri.detail', compact('document'));
    }

    public function listAktifAspri()
    {
        $documents = Document::with(['latestVersion', 'approvals.user'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DITINJAU', 'PENDING', 'SIAP_TTD'])
            ->latest()
            ->get();

        return view('app.aspri.list-aktif', compact('documents'));
    }

    public function riwayatAspri(Request $request)
    {
        $query = Document::with(['latestVersion', 'approvals.user'])
            ->where('user_id', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->orderBy('created_at', 'asc')->get();

        return view('app.aspri.riwayat', compact('documents'));
    }

    // Dashboard Petinggi Real
    public function indexPetinggi()
    {
        $approvals = DocumentApproval::with([
                'document.latestVersion',
                'document.user',
                'document.approvals.user',
                'document.comments.user'
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('app.petinggi.dashboard', compact('approvals'));
    }

    public function riwayatPetinggi(Request $request)
    {
        $userId = auth()->id();

        $query = DocumentApproval::with(['document.user', 'document.latestVersion'])
            ->where('user_id', $userId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $approvals = $query->latest('updated_at')->get();

        return view('app.petinggi.riwayat', compact('approvals'));
    }

    // Dashboard Dirut Real
    public function indexDirut()
    {
        $readyToSignDocuments = Document::with(['latestVersion', 'approvals.user'])
            ->where('status', 'SIAP_TTD')
            ->latest()
            ->get();

        return view('app.dirut.dashboard', compact('readyToSignDocuments'));
    }


    /*
    |--------------------------------------------------------------------------
    | 3. LOGIKA DAN AKSI SISTEM DOKUMEN (BACKEND)
    |--------------------------------------------------------------------------
    */

    // Simpan Pengajuan SK Baru (Oleh Aspri)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pdf_file' => 'required|mimes:pdf|max:10240',
            'petinggi_ids' => 'required|array',
        ]);

        $document = DB::transaction(function () use ($request) {
            $documentNumber = 'SK-' . date('Ym') . '-' . str_pad(
                Document::whereYear('created_at', date('Y'))
                    ->whereMonth('created_at', date('m'))
                    ->lockForUpdate()
                    ->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $file = request()->file('pdf_file');
            $filePath = $file->store('documents/v1', 'public');

            $document = Document::create([
                'user_id' => Auth::id() ?? 1,
                'title' => $request->title,
                'document_number' => $documentNumber,
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
                ]);
            }

            return $document;
        });

        return redirect()->back()->with('success', 'SK berhasil diajukan dan diteruskan ke para Petinggi!');
    }

    // Review SK oleh Petinggi (Terima / Pending / Tolak)
    public function reviewPetinggi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:TERIMA,PENDING,TOLAK',
            'comment' => 'nullable|string',
        ]);

        $userId = Auth::id() ?? $request->user_id;

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $approval->update(['status' => $request->status]);

        // Jika ada komentar diskusi
        if ($request->filled('comment')) {
            Comment::create([
                'document_id' => $id,
                'user_id' => $userId,
                'comment' => $request->comment,
            ]);
        }

        // Cek status kolektif dari seluruh petinggi
        $doc = Document::with('approvals')->findOrFail($id);
        
        if ($request->status === 'TOLAK') {
            $doc->update(['status' => 'DITOLAK']);
        } elseif ($request->status === 'PENDING') {
            $doc->update(['status' => 'PENDING']);
        } else {
            // Cek apakah SEMUA petinggi sudah setuju (TERIMA)
            $totalApprovals = $doc->approvals->count();
            $acceptedApprovals = $doc->approvals->where('status', 'TERIMA')->count();

            if ($totalApprovals === $acceptedApprovals) {
                $doc->update(['status' => 'SIAP_TTD']);
            }
        }

        return redirect()->back()->with('success', 'Keputusan Anda berhasil disimpan!');
    }


    // Logika Re-Upload
    public function uploadRevision(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $document = Document::with('versions')->findOrFail($id);

        if ($document->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Dokumen ini tidak sedang dalam status revisi.');
        }

        DB::transaction(function () use ($request, $document) {
            $file = $request->file('pdf_file');
            $filePath = $file->store('documents/v1', 'public');

            $nextVersion = $document->versions()->max('version_number') + 1;

            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => $nextVersion,
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
            ]);

            // Reset semua approval Petinggi karena dokumen berubah
            $document->approvals()->update(['status' => 'MENUNGGU']);

            $document->update(['status' => 'DITINJAU']);
        });

        return redirect()->back()->with('success', 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Petinggi.');
    }

    /*
    |--------------------------------------------------------------------------
    | AUTO-SEND (BACKEND)
    |--------------------------------------------------------------------------
    */

    public function sendReminder($id)
    {
        $document = Document::with('approvals.user')->findOrFail($id);

        $pendingApprovals = $document->approvals->where('status', 'MENUNGGU');

        if ($pendingApprovals->isEmpty()) {
            return redirect()->back()->with('error', 'Semua Petinggi sudah merespons, tidak ada yang perlu diingatkan.');
        }

        $terkirim = [];

        foreach ($pendingApprovals as $approval) {
            $user = $approval->user;

            if (!$user || !$user->phone) {
                continue;
            }

            $nomor = $this->formatNomorWA($user->phone);

            $pesan = "Halo {$user->name},\n\nAnda memiliki pengajuan SK yang menunggu peninjauan:\n\n"
                . "Judul: {$document->title}\n"
                . "No: {$document->document_number}\n\n"
                . "Mohon segera ditinjau melalui sistem SIGAP_V1.\n\nTerima kasih.";

            Http::withHeaders([
                'Authorization' => config('services.fonnte.token'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $nomor,
                'message' => $pesan,
            ]);

            $terkirim[] = $user->name;
        }

        if (empty($terkirim)) {
            return redirect()->back()->with('error', 'Tidak ada nomor WA valid untuk dikirimi reminder.');
        }

        return redirect()->back()->with('success', 'Reminder terkirim ke: ' . implode(', ', $terkirim));
    }

    private function formatNomorWA($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }

    public function signDirut(Request $request, $id)
    {
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf',
            'qr_hash' => 'required|string',
        ]);

        $doc = Document::with('latestVersion')->findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Petinggi!');
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

        return redirect()->route('app.dirut.dashboard')->with('success', 'Dokumen SK berhasil ditandatangani dan disahkan!');
    }

    public function showSignaturePage()
    {
        return view('app.dirut.signature');
    }

    public function saveSignature(Request $request)
    {
        $request->validate([
            'signature_image' => 'required|string',
        ]);

        // Data base64 dari canvas: "data:image/png;base64,iVBORw0KG..."
        $imageData = $request->signature_image;
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $imageBytes = base64_decode($imageData);

        $fileName = 'signatures/dirut_' . Auth::id() . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $imageBytes);

        $user = Auth::user();
        $user->update(['signature_path' => $fileName]);

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan!');
    }

    public function showTtdEditor($id)
    {
        $document = Document::with('latestVersion')->findOrFail($id);

        if ($document->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen ini belum siap untuk ditandatangani.');
        }

        if (!Auth::user()->signature_path) {
            return redirect()->route('dirut.signature.page')->with('error', 'Silakan atur tanda tangan Anda terlebih dahulu.');
        }

        return view('app.dirut.ttd-editor', compact('document'));
    }

}