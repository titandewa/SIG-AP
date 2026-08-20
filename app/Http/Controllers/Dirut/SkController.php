<?php

namespace App\Http\Controllers\Dirut;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\FcmService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SkController extends Controller
{
    protected FcmService $fcmService;
    protected WhatsAppNotificationService $waService;

    public function __construct(FcmService $fcmService, WhatsAppNotificationService $waService)
    {
        $this->fcmService = $fcmService;
        $this->waService = $waService;
    }

    // Dashboard Direksi Real
    public function index()
    {
        $readyToSignDocuments = Document::with(['latestVersion', 'approvals.user'])
            ->where('status', 'SIAP_TTD')
            ->latest()
            ->get();

        return view('app.dirut.dashboard', compact('readyToSignDocuments'));
    }

    public function riwayat(Request $request)
    {
        $query = Document::where('status', 'DISETUJUI')
            ->whereNotNull('signed_file_path')
            ->with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest('updated_at')->get();

        return view('app.dirut.riwayat', compact('documents'));
    }

    public function sign(Request $request, $id)
    {
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf',
            'qr_hash' => 'required|string',
        ]);

        $doc = Document::with('latestVersion')->findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Validator!');
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

        // Kirim WA + FCM ke Administrator bahwa dokumen sudah selesai ditandatangani sepenuhnya
        $doc->load('user');
        if ($doc->user) {
            $this->waService->sendWaNotifAspri($doc->user, $doc, 'SELESAI & TELAH DITANDATANGANI (Sah)');

            $this->fcmService->sendToUser(
                $doc->user,
                'Dokumen Selesai Ditandatangani',
                "Dokumen \"{$doc->title}\" telah sah ditandatangani oleh Direksi.",
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
        $document = Document::with(['latestVersion', 'signaturePlacements.user'])->findOrFail($id);

        if ($document->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen ini belum siap untuk ditandatangani.');
        }

        if (!Auth::user()->signature_path) {
            return redirect()->route('dirut.signature.page')->with('error', 'Silakan atur tanda tangan Anda terlebih dahulu.');
        }

        return view('app.dirut.ttd-editor', compact('document'));
    }

    public function signWithoutSignature(Request $request, $id)
    {
        $doc = Document::with('latestVersion')->findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Validator!');
        }

        // Salin PDF asli (belum ada TTD) sebagai placeholder signed_file_path
        $originalPath = $doc->latestVersion->file_path;
        $signedFileName = 'signed_' . $doc->document_number . '_' . time() . '.pdf';
        $signedRelativePath = 'documents/signed/' . $signedFileName;

        Storage::disk('public')->put(
            $signedRelativePath,
            Storage::disk('public')->get($originalPath)
        );

        $fullPath = storage_path('app/public/' . $signedRelativePath);
        $fileHash = hash_file('sha256', $fullPath);

        $doc->update([
            'status' => 'DISETUJUI_TANPA_TTD',
            'approved_without_signature_at' => now(),
            'signed_file_path' => $signedRelativePath,
            'file_hash' => $fileHash,
        ]);

        // Notif ke aspri: dokumen sah, tapi TTD belum ditempel — perlu aspri lengkapi
        $doc->load('user');
        if ($doc->user) {
            $this->waService->sendWaNotifAspri($doc->user, $doc, 'DISAHKAN — MENUNGGU PENEMPELAN TTD');

            $this->fcmService->sendToUser(
                $doc->user,
                'Dokumen Disahkan, Perlu Ditempel TTD',
                "Dokumen \"{$doc->title}\" sudah disahkan Direksi. Silakan tempelkan tanda tangan.",
                [
                    'document_id' => (string) $doc->id,
                    'type' => 'perlu_ttd_aspri',
                    'url' => route('aspri.detail', $doc->id), // sesuaikan kalau nanti ada route khusus
                ]
            );
        }

        return redirect()->route('app.dirut.dashboard')->with('success', 'Dokumen berhasil disahkan tanpa TTD. Aspri akan melengkapi tanda tangan.');
    }
}