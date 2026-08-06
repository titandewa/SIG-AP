<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentVersion;
use App\Models\Comment;
use App\Models\User;
use App\Services\FcmService;
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
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

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
            ->where('user_id', Auth::id())
            ->whereIn('status', ['DISETUJUI', 'DITOLAK']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->orderBy('updated_at', 'desc')->get();

        return view('app.aspri.riwayat', compact('documents'));
    }

    // Dashboard Petinggi Real
    public function indexPetinggi()
    {
        $approvals = DocumentApproval::with([
                'document.latestVersion',
                'document.versions',
                'document.user',
                'document.approvals.user',
                'document.comments.user'
            ])
            ->where('user_id', Auth::id())
            ->whereHas('document', function ($q) {
                $q->where('status', 'DITINJAU');
            })
            ->latest()
            ->get();

        return view('app.petinggi.dashboard', compact('approvals'));
    }

    public function riwayatPetinggi(Request $request)
    {
        $userId = auth()->id();

        $query = DocumentApproval::with(['document.user', 'document.latestVersion'])
            ->where('user_id', $userId)
            ->whereIn('status', ['TERIMA', 'TOLAK']);

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

    public function riwayatDirut()
    {
        $documents = Document::where('status', 'DISETUJUI')
            ->whereNotNull('signed_file_path')
            ->with('user')
            ->latest('updated_at')
            ->get();

        return view('app.dirut.riwayat', compact('documents'));
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
            'document_number' => 'required|string|unique:documents,document_number',
            'pdf_file' => 'required|mimes:pdf|max:10240',
            'petinggi_ids' => 'required|array',
        ]);

        $document = DB::transaction(function () use ($request) {
            $file = $request->file('pdf_file');
            $filePath = $file->store('documents/v1', 'public');

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
                ]);
            }

            return $document;
        });

        // Kirim WA + FCM notif ke semua petinggi yang jadi reviewer
        $document->load('approvals.user');
        foreach ($document->approvals as $approval) {
            $this->sendWaPengajuanBaru($approval->user, $document);

            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Pengajuan SK Baru',
                    "Ada pengajuan SK baru dari Aspri: \"{$document->title}\" perlu Anda tinjau.",
                    [
                        'document_id' => (string) $document->id,
                        'type' => 'pengajuan_baru',
                        'url' => route('app.petinggi.dashboard'),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'SK berhasil diajukan dan diteruskan ke para Petinggi!');
    }

    public function versionHistory($id)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($id);
        $versions = $document->versions()->orderBy('version_number', 'desc')->get();

        return view('app.aspri.version-history', compact('document', 'versions'));
    }

    private function sendWaPengajuanBaru($user, $document)
    {
        if (!$user || !$user->phone) {
            return;
        }

        $nomor = $this->formatNomorWA($user->phone);
        $link = route('app.petinggi.dashboard');
        $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');

        $pesan = "Halo {$user->name},\n\nAda pengajuan SK baru yang perlu Anda tinjau:\n\n"
            . "Judul: {$document->title}\n"
            . "No: {$document->document_number}\n"
            . "Diajukan: {$tanggalUpload}\n\n"
            . "Silakan tinjau melalui link berikut:\n{$link}\n\nTerima kasih.";

        Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
        ]);
    }

    private function sendWaNotifAspri($user, $document, $statusPesan)
    {
        if (!$user || !$user->phone) {
            return;
        }

        $nomor = $this->formatNomorWA($user->phone);
        $link = route('aspri.detail', $document->id);

        $pesan = "Halo {$user->name},\n\nUpdate status dokumen Anda:\n\n"
            . "Judul: {$document->title}\n"
            . "No: {$document->document_number}\n"
            . "Status Terbaru: *{$statusPesan}*\n\n"
            . "Silakan cek melalui sistem SIGAP:\n{$link}\n\nTerima kasih.";

        Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
        ]);
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

        // Guard: dokumen sudah diputuskan (ditolak/revisi/disetujui semua) oleh proses lain
        if ($approval->document->status !== 'DITINJAU') {
            return redirect()->back()->with('error', 'Dokumen ini sudah tidak dalam status ditinjau, keputusan tidak disimpan.');
        }

        // Guard: kamu sudah pernah merespon approval ini
        if ($approval->status !== 'MENUNGGU') {
            return redirect()->back()->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

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
        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        if ($request->status === 'TOLAK') {
            $doc->update(['status' => 'DITOLAK']);

            // Kirim WA + FCM ke Aspri (Dokumen Ditolak) — hanya aspri pengirim
            if ($doc->user) {
                $this->sendWaNotifAspri($doc->user, $doc, 'DITOLAK oleh Petinggi');

                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Ditolak',
                    "Dokumen \"{$doc->title}\" ditolak oleh Petinggi.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'ditolak',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }
        } elseif ($request->status === 'PENDING') {
            $doc->update(['status' => 'PENDING']);

            // Kirim WA + FCM ke Aspri (Dokumen Perlu Revisi / Pending) — hanya aspri pengirim
            if ($doc->user) {
                $this->sendWaNotifAspri($doc->user, $doc, 'PENDING / PERLU REVISI');

                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Perlu Revisi',
                    "Dokumen \"{$doc->title}\" perlu direvisi sesuai catatan Petinggi.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'revisi',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }
        } else {
            // Cek apakah SEMUA petinggi sudah setuju (TERIMA)
            $totalApprovals = $doc->approvals->count();
            $acceptedApprovals = $doc->approvals->where('status', 'TERIMA')->count();

            if ($totalApprovals === $acceptedApprovals) {
                $doc->update(['status' => 'SIAP_TTD']);

                // Kirim WA + FCM ke Dirut bahwa dokumen siap ditandatangani
                $dirut = User::where('role', 'dirut')->first(); // Ambil user yang rolenya dirut
                if ($dirut) {
                    $this->sendWaNotifDirut($dirut, $doc);

                    $this->fcmService->sendToUser(
                        $dirut,
                        'Dokumen Siap TTD',
                        "Dokumen \"{$doc->title}\" telah disetujui semua Petinggi dan siap ditandatangani.",
                        [
                            'document_id' => (string) $doc->id,
                            'type' => 'siap_ttd',
                            'url' => route('dirut.ttd.editor', $doc->id),
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Keputusan Anda berhasil disimpan!');
    }

    private function sendWaNotifDirut($dirut, $document)
    {
        if (!$dirut || !$dirut->phone) {
            return;
        }

        $nomor = $this->formatNomorWA($dirut->phone);
        $link = route('app.dirut.dashboard'); // Sesuaikan route dashboard dirut kamu

        $pesan = "Halo {$dirut->name},\n\nSemua Petinggi telah menyetujui pengajuan dokumen:\n\n"
            . "Judul: {$document->title}\n"
            . "No: {$document->document_number}\n\n"
            . "Dokumen kini *SIAP DITANDATANGANI*. Silakan akses sistem SIGAP:\n{$link}\n\nTerima kasih.";

        Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
        ]);
    }


    // Logika Re-Upload
    public function uploadRevision(Request $request, $id)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        $document = Document::with('versions', 'approvals.user')->findOrFail($id);

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

        // Kirim FCM ke semua Petinggi reviewer bahwa ada revisi baru untuk ditinjau ulang
        foreach ($document->approvals as $approval) {
            if ($approval->user) {
                $this->fcmService->sendToUser(
                    $approval->user,
                    'Revisi SK Diunggah',
                    "Aspri telah mengunggah revisi dokumen \"{$document->title}\", perlu ditinjau ulang.",
                    ['document_id' => (string) $document->id, 'type' => 'revisi_diunggah']
                );
            }
        }

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
        $link = route('app.petinggi.dashboard');
        $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');
        $lamaNganggur = $document->created_at->diffForHumans(null, true); // contoh: "3 jam"

        foreach ($pendingApprovals as $approval) {
            $user = $approval->user;

            if (!$user || !$user->phone) {
                continue;
            }

            $nomor = $this->formatNomorWA($user->phone);

            $pesan = "Halo {$user->name},\n\nAnda memiliki pengajuan SK yang menunggu peninjauan:\n\n"
                . "Judul: {$document->title}\n"
                . "No: {$document->document_number}\n"
                . "Diajukan: {$tanggalUpload} (sudah {$lamaNganggur})\n\n"
                . "Mohon segera ditinjau melalui link berikut:\n{$link}\n\nTerima kasih.";

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

        // Kirim WA + FCM ke Aspri bahwa dokumen sudah selesai ditandatangani sepenuhnya
        $doc->load('user');
        if ($doc->user) {
            $this->sendWaNotifAspri($doc->user, $doc, 'SELESAI & TELAH DITANDATANGANI (Sah)');

            $this->fcmService->sendToUser(
                $doc->user,
                'Dokumen Selesai Ditandatangani',
                "Dokumen \"{$doc->title}\" telah sah ditandatangani oleh Dirut.",
                [
                    'document_id' => (string) $doc->id,
                    'type' => 'selesai_ttd',
                    'url' => route('aspri.detail', $doc->id),
                ]
            );
        }

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

        $user = Auth::user();

        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $imageData = $request->signature_image;
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $imageBytes = base64_decode($imageData);

        $fileName = 'signatures/dirut_' . Auth::id() . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $imageBytes);

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

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $document = Document::with('approvals')->findOrFail($id);
        $userId = Auth::id();

        $isOwner = $document->user_id === $userId;
        $isReviewer = $document->approvals->contains('user_id', $userId);

        if (!$isOwner && !$isReviewer) {
            abort(403, 'Kamu tidak punya akses ke diskusi dokumen ini.');
        }

        Comment::create([
            'document_id' => $document->id,
            'user_id' => $userId,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'Pesan terkirim.');
    }

}