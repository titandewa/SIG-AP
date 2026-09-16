<?php

namespace App\Http\Controllers\Dirut;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\SignaturePlacement;
use App\Services\FcmService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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

    // Dashboard Direksi
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
            ->where('role', 'direksi')
            ->whereHas('document', function ($q) {
                $q->whereIn('status', ['DITINJAU_DIREKSI', 'PENDING_DIREKSI']);
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

        return view('app.dirut.dashboard', compact('approvals', 'focusDocId'));
    }

    public function riwayat(Request $request)
    {
        $userId = Auth::id();

        $query = DocumentApproval::with(['document.user', 'document.latestVersion', 'document.approvals.user'])
            ->where('user_id', $userId)
            ->where('role', 'direksi')
            ->whereIn('status', ['TERIMA', 'DIBATALKAN']);

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

        return view('app.dirut.riwayat', compact('approvals'));
    }

    // Review SK oleh Direksi (Terima / Pending — tanpa Tolak)
    public function review(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:TERIMA,PENDING',
            'comment' => 'nullable|string',
            'signed_pdf' => 'nullable|file|mimes:pdf',
        ]);

        $userId = Auth::id() ?? $request->user_id;

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->where('role', 'direksi')
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU_DIREKSI') {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau Direksi, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        if ($request->filled('comment')) {
            Comment::create([
                'document_id' => $id,
                'user_id' => $userId,
                'comment' => $request->comment,
            ]);
        }

        if ($request->status === 'TERIMA' && $approval->need_sign) {
            return redirect()->route('dirut.ttd.editor', $id);
        }

        $approval->update(['status' => $request->status]);

        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        if ($request->status === 'PENDING') {
            $doc->update(['status' => 'PENDING_DIREKSI']);

            ActivityLogger::log(
                action: 'revisi',
                description: Auth::user()->name . " (Direksi) meminta revisi SK \"{$doc->title}\"" . ($request->filled('comment') ? ": {$request->comment}" : ''),
                documentId: $doc->id,
                meta: ['comment' => $request->comment ?? null]
            );

            if ($doc->user) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'PENDING / PERLU REVISI DARI DIREKSI');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Perlu Revisi',
                    "Dokumen \"{$doc->title}\" perlu direvisi sesuai catatan Direksi.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'revisi',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }
        } else {
            ActivityLogger::log(
                action: 'setuju',
                description: Auth::user()->name . " (Direksi) menyetujui SK \"{$doc->title}\"",
                documentId: $doc->id
            );

            $flattenedPdfPath = null;
            if ($request->hasFile('signed_pdf')) {
                $signedFile = $request->file('signed_pdf');
                $safeDocNumber = Str::slug($doc->document_number);
                $tempSignedName = 'signed_' . $safeDocNumber . '_' . time() . '.pdf';
                $flattenedPdfPath = 'documents/signed/' . $tempSignedName;
                Storage::disk('public')->put($flattenedPdfPath, file_get_contents($signedFile));
            }

            $this->finalizeTerima($doc, $flattenedPdfPath);
        }

        return redirect()->route('app.dirut.dashboard')->with('success', 'Keputusan Anda berhasil disimpan!');
    }

    /**
     * Finalisasi dokumen setelah semua Direksi menyetujui.
     *
     * @param Document $doc
     * @param string|null $signedPdfPath Path PDF yang sudah ditandatangani (jika ada).
     * @param bool $withoutSignature True jika Direksi menyetujui TANPA menempel TTD
     *                               (TTD dititipkan ke Admin untuk ditempel nanti).
     */
    private function finalizeTerima(Document $doc, ?string $signedPdfPath = null, bool $withoutSignature = false)
    {
        $direksiApprovals = $doc->approvals->where('role', 'direksi');
        $totalApprovals = $direksiApprovals->count();
        $acceptedApprovals = $direksiApprovals->where('status', 'TERIMA')->count();

        if ($totalApprovals !== $acceptedApprovals) {
            if ($signedPdfPath) {
                Storage::disk('public')->delete($signedPdfPath);
            }
            return;
        }

        // Kasus: disetujui tapi TTD dititipkan ke Admin -> BELUM final.
        if ($withoutSignature) {
            $doc->update([
                'status' => 'DISETUJUI_TANPA_TTD',
            ]);

            NotificationHistory::where('data->document_id', (string) $doc->id)->delete();

            if ($doc->user) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'DISETUJUI_TANPA_TTD');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Disetujui (Menunggu TTD)',
                    "Dokumen \"{$doc->title}\" telah disetujui oleh semua Direksi dan menunggu penempelan TTD oleh Admin.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'disetujui_tanpa_ttd',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            }

            return;
        }

        // Kasus normal: sudah ada TTD (baik dari flow review biasa maupun ttd-editor).
        if (!$signedPdfPath) {
            $signedPdfPath = $doc->latestVersion->file_path;
        }

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
                "Dokumen \"{$doc->title}\" telah disahkan oleh semua Direksi.",
                [
                    'document_id' => (string) $doc->id,
                    'type' => 'disetujui',
                    'url' => route('aspri.detail', $doc->id),
                ]
            );
        }
    }

    public function showTtdEditor($id)
    {
        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', Auth::id())
            ->where('role', 'direksi')
            ->firstOrFail();

        if (!$approval->need_sign) {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Kamu tidak butuh menempelkan TTD untuk dokumen ini.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        if (!Auth::user()->signature_path) {
            return redirect()->route('dirut.signature.page')->with('error', 'Silakan atur tanda tangan Anda terlebih dahulu.');
        }

        $document = Document::with(['latestVersion', 'signaturePlacements.user'])->findOrFail($id);

        return view('app.dirut.ttd-editor', compact('document', 'approval'));
    }

    public function saveSignaturePlacement(Request $request, $id)
    {
        $request->validate([
            'placements' => 'required|string',
            'signed_pdf' => 'required|file|mimes:pdf',
            //'qr_hash' => 'nullable|string',
        ]);

        $placements = json_decode($request->placements, true) ?? [];

        $userId = Auth::id();

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->where('role', 'direksi')
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU_DIREKSI') {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau Direksi, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            return redirect()->route('app.dirut.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
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
            description: Auth::user()->name . " (Direksi) menyetujui dan menandatangani SK \"{$doc->title}\"",
            documentId: $doc->id
        );

        $signedFile = $request->file('signed_pdf');
        $safeDocNumber = Str::slug($doc->document_number);
        $tempSignedName = 'signed_' . $safeDocNumber . '_' . time() . '.pdf';
        $tempSignedPath = 'documents/signed/' . $tempSignedName;
        Storage::disk('public')->put($tempSignedPath, file_get_contents($signedFile));

        $this->finalizeTerima($doc, $tempSignedPath);

        return redirect()->route('app.dirut.dashboard')->with('success', 'Tanda tangan berhasil ditempatkan dan keputusan disimpan!');
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

        return redirect()->route('app.dirut.dashboard')->with('success', 'Tanda tangan berhasil disimpan!');
    }

    public function signWithoutSignature(Request $request, $id)
    {
        $userId = Auth::id() ?? $request->user_id;

        $approval = DocumentApproval::where('document_id', $id)
            ->where('user_id', $userId)
            ->where('role', 'direksi')
            ->firstOrFail();

        if ($approval->document->status !== 'DITINJAU_DIREKSI') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Dokumen ini sudah tidak dalam status ditinjau Direksi, keputusan tidak disimpan.'], 422);
            }
            return redirect()->route('app.dirut.dashboard')->with('error', 'Dokumen ini sudah tidak dalam status ditinjau Direksi, keputusan tidak disimpan.');
        }

        if ($approval->status !== 'MENUNGGU') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.'], 422);
            }
            return redirect()->route('app.dirut.dashboard')->with('error', 'Kamu sudah pernah memberikan keputusan untuk dokumen ini.');
        }

        $approval->update(['status' => 'TERIMA']);

        $doc = Document::with(['approvals', 'user'])->findOrFail($id);

        ActivityLogger::log(
            action: 'setuju_tanpa_ttd',
            description: Auth::user()->name . " (Direksi) menyetujui SK \"{$doc->title}\" dan menitipkan TTD ke Admin",
            documentId: $doc->id
        );

        $this->finalizeTerima($doc, null, true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen disetujui tanpa tanda tangan. Admin akan menempelkan TTD Anda nanti.'
            ]);
        }

        return redirect()->route('app.dirut.dashboard')->with('success', 'Dokumen disetujui tanpa tanda tangan. Admin akan menempelkan TTD Anda nanti.');
    }
}