<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Document;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected WhatsAppNotificationService $waService;

    public function __construct(WhatsAppNotificationService $waService)
    {
        $this->waService = $waService;
    }

    /*
    |--------------------------------------------------------------------------
    | ROUTE / WORKSPACE DEMO (Bahan Presentasi Atasan)
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
    | AKSI LINTAS ROLE
    |--------------------------------------------------------------------------
    */

    public function sendReminder($id)
    {
        $document = Document::with('approvals.user')->findOrFail($id);

        $pendingApprovals = $document->approvals->where('status', 'MENUNGGU');

        if ($pendingApprovals->isEmpty()) {
            return redirect()->back()->with('error', 'Semua Validator sudah merespons, tidak ada yang perlu diingatkan.');
        }

        $terkirim = [];
        $link = route('app.petinggi.dashboard');
        $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');
        $salam = $this->waService->salamWaktu();

        foreach ($pendingApprovals as $approval) {
            $user = $approval->user;

            $terkirim_berhasil = $this->waService->sendWaReminder($user, $document, $salam, $tanggalUpload, $link);

            if ($terkirim_berhasil) {
                $terkirim[] = $user->name;
            }
        }

        if (empty($terkirim)) {
            return redirect()->back()->with('error', 'Tidak ada nomor WA valid untuk dikirimi reminder.');
        }

        return redirect()->back()->with('success', 'Reminder terkirim ke: ' . implode(', ', $terkirim));
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

        $comment = Comment::create([
            'document_id' => $document->id,
            'user_id' => $userId,
            'comment' => $request->comment,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->diffForHumans(),
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Pesan terkirim.');
    }
}