<?php

namespace App\Http\Controllers\Petinggi;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;
use App\Services\FcmService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SignaturePlacement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    // Dashboard Validator Real
    public function index(Request $request)
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
                $q->whereIn('status', ['DITINJAU', 'PENDING']);
            })
            ->orderByRaw("CASE
                WHEN status = 'MENUNGGU' THEN 0
                WHEN status = 'PENDING' THEN 1
                WHEN status = 'TERIMA' THEN 2
                ELSE 3
            END")
            ->orderBy('created_at', 'asc')
            ->get();

        $focusDocId = $request->query('doc');

        return view('app.petinggi.dashboard', compact('approvals', 'focusDocId'));
    }

    public function riwayat(Request $request)
    {
        $userId = auth()->id();

        $query = DocumentApproval::with(['document.user', 'document.latestVersion', 'document.approvals.user'])
            ->where('user_id', $userId)
            ->whereIn('status', ['TERIMA', 'TOLAK', 'DIBATALKAN']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('document', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $approvals = $query->latest('updated_at')->get();

        return view('app.petinggi.riwayat', compact('approvals'));
    }

    // Review SK oleh Validator (Terima / Pending / Tolak)
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:TERIMA,PENDING,TOLAK',
            'comment' => 'nullable|string',
        ]);

        $userId = Auth::id() ?? $request->user_id;

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU') {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        // Simpan komentar dulu (kalau ada), sebelum redirect ke TTD
        if ($request->filled('comment')) {
            Comment::create([
                'document_id' => $id,
                'user_id' => $userId,
                'comment' => $request->comment,
            ]);
        }

        // Kalau TERIMA dan butuh TTD → jangan update status dulu, redirect ke halaman TTD
        if ($request->status === 'TERIMA' && $approval->need_sign) {
            return redirect()->route('petinggi.ttd.editor', $id);
        }

        $approval->update(['status' => $request->status]);

        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        if ($request->status === 'TOLAK') {
            $doc->update(['status' => 'DITOLAK']);
            DocumentApproval::where('document_id', $doc->id)
                ->where('status', 'MENUNGGU')
                ->update(['status' => 'DIBATALKAN']);

            ActivityLogger::log(
                action: 'tolak',
                description: Auth::user()->name . " (Validator) menolak SK \"{$doc->title}\"" . ($request->filled('comment') ? ": {$request->comment}" : ''),
                documentId: $doc->id,
                meta: ['comment' => $request->comment ?? null]
            );

            if ($doc->user) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'DITOLAK oleh Validator');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Ditolak',
                    "Dokumen \"{$doc->title}\" ditolak oleh Validator.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'ditolak',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }
        } elseif ($request->status === 'PENDING') {
            $doc->update(['status' => 'PENDING']);

            ActivityLogger::log(
                action: 'revisi',
                description: Auth::user()->name . " (Validator) meminta revisi SK \"{$doc->title}\"" . ($request->filled('comment') ? ": {$request->comment}" : ''),
                documentId: $doc->id,
                meta: ['comment' => $request->comment ?? null]
            );

            if ($doc->user) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'PENDING / PERLU REVISI');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Perlu Revisi',
                    "Dokumen \"{$doc->title}\" perlu direvisi sesuai catatan Validator.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'revisi',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }
        } else {
            // status TERIMA dan need_sign = false
            ActivityLogger::log(
                action: 'setuju',
                description: Auth::user()->name . " (Validator) menyetujui SK \"{$doc->title}\"",
                documentId: $doc->id
            );

            $this->finalizeTerima($doc);
        }

        return redirect()->route('app.petinggi.dashboard')->with('success', 'Keputusan Anda berhasil disimpan!');
    }

    private function finalizeTerima(Document $doc, ?string $signedPdfPath = null)
    {
        $totalApprovals = $doc->approvals->count();
        $acceptedApprovals = $doc->approvals->where('status', 'TERIMA')->count();

        if ($totalApprovals !== $acceptedApprovals) {
            // Belum selesai semua, file PDF sementara (kalau ada) tidak dipakai
            if ($signedPdfPath) {
                Storage::disk('public')->delete($signedPdfPath);
            }
            return;
        }

        // Kalau tidak ada file hasil tempel TTD sama sekali (tidak ada Validator yang need_sign),
        // pakai dokumen asli dari Admin sebagai file final.
        if (!$signedPdfPath) {
            $signedPdfPath = $doc->latestVersion->file_path;
        }

        if (!$doc->need_direksi) {
            $fullPath = storage_path('app/public/' . $signedPdfPath);
            $fileHash = hash_file('sha256', $fullPath);

            $doc->update([
                'status' => 'DISETUJUI',
                'signed_file_path' => $signedPdfPath,
                'signed_at' => now(),
                'file_hash' => $fileHash,
            ]);

            NotificationHistory::where('data->document_id', (string) $doc->id)->delete();

            if ($doc->user) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'DISETUJUI');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Disetujui',
                    "Dokumen \"{$doc->title}\" telah disetujui oleh semua Validator.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'disetujui',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }

            return;
        }

        // Butuh direksi, file sementara tadi tidak dipakai (dirut yang flatten ulang nanti)
        if ($signedPdfPath !== $doc->latestVersion->file_path) {
            Storage::disk('public')->delete($signedPdfPath);
        }

        $doc->update(['status' => 'SIAP_TTD']);

        $dirut = User::where('id', $doc->dirut_id)->where('role', 'dirut')->first();
        if ($dirut) {
            $this->waService->sendWaNotifDirut($dirut, $doc);
            $this->fcmService->sendToUser(
                $dirut,
                'Dokumen Siap TTD',
                "Dokumen \"{$doc->title}\" telah disetujui semua Validator dan siap ditandatangani.",
                [
                    'document_id' => (string) $doc->id,
                    'type' => 'siap_ttd',
                    'url' => route('dirut.ttd.editor', $doc->id),
                ]
            );
        }
    }

    public function showTtdEditor($id)
    {
        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$approval->need_sign) {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Kamu tidak butuh menempelkan TTD untuk dokumen ini.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        if (!Auth::user()->signature_path) {
            return redirect()->route('petinggi.signature.page')->with('error', 'Silakan atur tanda tangan Anda terlebih dahulu.');
        }

        $document = Document::with('latestVersion')->findOrFail($id);

        return view('app.petinggi.ttd-editor', compact('document', 'approval'));
    }

    public function saveSignaturePlacement(Request $request, $id)
    {
        $request->validate([
            'placements' => 'required|string',
            'signed_pdf' => 'required|file|mimes:pdf',
        ]);

        $placements = json_decode($request->placements, true) ?? [];

        $userId = Auth::id();

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU') {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        foreach ($placements as $p) {
            SignaturePlacement::create([
                'document_id' => $id,
                'document_approval_id' => $approval->id,
                'user_id' => $userId,
                'page_number' => $p['page_number'],
                'x' => $p['x'],
                'y' => $p['y'],
                'width' => $p['width'],
                'height' => $p['height'],
                'signature_path' => Auth::user()->signature_path,
            ]);
        }

        $approval->update(['status' => 'TERIMA']);

        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        ActivityLogger::log(
            action: 'setuju_ttd',
            description: Auth::user()->name . " (Validator) menyetujui dan menandatangani SK \"{$doc->title}\"",
            documentId: $doc->id
        );

        // Simpan PDF hasil flatten dari browser dulu (sementara), baru dipakai kalau ini penyelesaian
        $signedFile = $request->file('signed_pdf');
        $safeDocNumber = Str::slug($doc->document_number);
        $tempSignedName = 'signed_' . $safeDocNumber . '_' . time() . '.pdf';
        $tempSignedPath = 'documents/signed/' . $tempSignedName;
        Storage::disk('public')->put($tempSignedPath, file_get_contents($signedFile));

        $this->finalizeTerima($doc, $tempSignedPath);

        return redirect()->route('app.petinggi.dashboard')->with('success', 'Tanda tangan berhasil ditempatkan dan keputusan disimpan!');
    }

    public function showSignaturePage()
    {
        return view('app.petinggi.signature');
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

        $fileName = 'signatures/petinggi_' . Auth::id() . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $imageBytes);

        $user->update(['signature_path' => $fileName]);

        return redirect()->route('app.petinggi.dashboard')->with('success', 'Tanda tangan berhasil disimpan!');
    }

    public function signWithoutSignature(Request $request, $id)
    {
        $userId = Auth::id() ?? $request->user_id;

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Dokumen ini sudah tidak dalam status ditinjau, keputusan tidak disimpan.'], 422);
            }
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.'], 422);
            }
            return redirect()->route('app.petinggi.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        $approval->update(['status' => 'TERIMA']);

        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        ActivityLogger::log(
            action: 'setuju_tanpa_ttd',
            description: Auth::user()->name . " (Validator) menyetujui SK \"{$doc->title}\" dan menitipkan TTD ke Admin",
            documentId: $doc->id
        );

        $this->finalizeTerima($doc);

        // Jika request dari Fetch/AJAX, kembalikan JSON agar tidak redirect loop
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Dokumen disetujui tanpa tanda tangan. Admin akan menempelkan TTD Anda nanti.'
            ]);
        }

        return redirect()->route('app.petinggi.dashboard')->with('success', 'Dokumen disetujui tanpa tanda tangan. Admin akan menempelkan TTD Anda nanti.');
    }
}