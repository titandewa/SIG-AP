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
        $document = Document::where('qr_code_hash', $hash)->firstOrFail();
        
        return view('demo.verify', compact('document'));
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
        $documents = Document::with(['versions', 'approvals.user', 'latestVersion'])->latest()->get();

        return view('app.aspri.dashboard', compact('petinggiList', 'documents'));
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
            'pdf_file' => 'required|mimes:pdf|max:10240', // Max 10MB
            'petinggi_ids' => 'required|array',
        ]);

        // Generate Nomor Dokumen Otomatis (format: SK-202607-0001)
        $documentNumber = 'SK-' . date('Ym') . '-' . str_pad(Document::count() + 1, 4, '0', STR_PAD_LEFT);

        // Upload Berkas PDF
        $file = $request->file('pdf_file');
        $filePath = $file->store('documents/v1', 'public');

        // Buat Record Dokumen Utama
        $document = Document::create([
            'user_id' => Auth::id() ?? 1,
            'title' => $request->title,
            'document_number' => $documentNumber,
            'status' => 'DITINJAU',
        ]);

        // Versi 1 Berkas
        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => 1,
            'file_path' => $filePath,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        // Daftarkan Petinggi yang Harus Review
        foreach ($request->petinggi_ids as $petinggiId) {
            DocumentApproval::create([
                'document_id' => $document->id,
                'user_id' => $petinggiId,
                'status' => 'MENUNGGU',
            ]);
        }

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

    // Eksekusi TTD Digital oleh Dirut
    public function signDirut(Request $request, $id)
    {
        $request->validate([
            'position' => 'nullable|string|in:center,left,right',
        ]);

        $doc = Document::findOrFail($id);

        if ($doc->status !== 'SIAP_TTD') {
            return redirect()->back()->with('error', 'Dokumen belum mendapat persetujuan 100% dari Petinggi!');
        }

        // Generate Hash Unik Verifikasi QR Code
        $qrHash = 'SIGAP-' . date('Ymd') . '-' . Str::random(8);

        $doc->update([
            'status' => 'DISETUJUI',
            'qr_code_hash' => $qrHash,
            'stamp_position' => $request->position ?? 'center',
            'signed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Dokumen SK berhasil ditandatangani dan disahkan!');
    }
}