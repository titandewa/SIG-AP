<?php

namespace App\Http\Controllers\Dirut;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\FcmService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'placements' => 'nullable|string',
        ]);

        $doc = Document::with('latestVersion')->findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Validator!');
        }

        $belumDitempel = $doc->approvals()
            ->where('need_sign', true)
            ->whereDoesntHave('signaturePlacement')
            ->exists();

        $signedFile = $request->file('signed_pdf');
        $safeDocNumber = Str::slug($doc->document_number);
        $signedFileName = 'signed_' . $safeDocNumber . '_' . time() . '.pdf';
        $signedRelativePath = 'documents/signed/' . $signedFileName;
        Storage::disk('public')->put($signedRelativePath, file_get_contents($signedFile));

        $fullPath = storage_path('app/public/' . $signedRelativePath);
        $fileHash = hash_file('sha256', $fullPath);

        // Simpan posisi TTD dirut ke signature_placements
        if ($request->filled('placements')) {
            $placements = json_decode($request->placements, true) ?? [];
            foreach ($placements as $p) {
                \App\Models\SignaturePlacement::create([
                    'document_id' => $id,
                    'document_approval_id' => null,
                    'user_id' => Auth::id(),
                    'page_number' => $p['page_number'],
                    'x' => $p['x'],
                    'y' => $p['y'],
                    'width' => $p['width'],
                    'height' => $p['height'],
                    'signature_path' => Auth::user()->signature_path,
                ]);
            }
        }

        $doc->update([
            'status' => $belumDitempel ? 'DISETUJUI_TANPA_TTD' : 'DISETUJUI',
            'qr_code_hash' => $request->qr_hash,
            'signed_at' => now(),
            'signed_file_path' => $signedRelativePath,
            'file_hash' => $fileHash,
        ]);

        $doc->load('user');
        if ($doc->user) {
            if ($belumDitempel) {
                $this->waService->sendWaNotifAspri($doc->user, $doc, 'DISAHKAN — MENUNGGU PENEMPELAN TTD VALIDATOR');
                $this->fcmService->sendToUser(
                    $doc->user,
                    'Dokumen Disahkan, Perlu Ditempel TTD Validator',
                    "Dokumen \"{$doc->title}\" sudah ditandatangani Direksi, namun masih ada TTD Validator yang perlu Anda tempelkan.",
                    [
                        'document_id' => (string) $doc->id,
                        'type' => 'perlu_ttd_aspri',
                        'url' => route('aspri.detail', $doc->id),
                    ]
                );
            } else {
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
        }

        $successMsg = $belumDitempel
            ? 'Dokumen berhasil ditandatangani. Masih ada TTD Validator yang perlu ditempelkan Admin.'
            : 'Dokumen SK berhasil ditandatangani dan disahkan!';

        return redirect()->route('app.dirut.dashboard')->with('success', $successMsg);
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
        $doc = Document::with(['latestVersion', 'approvals'])->findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Validator!');
        }

        // CEK TAMBAHAN: Pastikan tidak ada validator titip TTD yang belum diselesaikan admin
        $hasUnfinishedDelegatedSignatures = $doc->approvals()
            ->where('need_sign', true)
            ->exists();

        if ($hasUnfinishedDelegatedSignatures) {
            return redirect()->back()->with('error', 'Masih ada validator yang menitipkan TTD dan belum diselesaikan oleh Admin!');
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
                    'url' => route('aspri.detail', $doc->id),
                ]
            );
        }

        return redirect()->route('app.dirut.dashboard')->with('success', 'Dokumen berhasil disahkan tanpa TTD. Aspri akan melengkapi tanda tangan.');
    }
}