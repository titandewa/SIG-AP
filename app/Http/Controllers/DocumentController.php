<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Document;
use App\Models\User;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /**
     * Pastikan user yang sedang login berhak akses diskusi dokumen ini
     * (pemilik dokumen atau salah satu reviewer-nya).
     */
    private function assertCanAccessDiscussion(Document $document, int $userId): void
    {
        $isOwner = $document->user_id === $userId;
        $isReviewer = $document->approvals->contains('user_id', $userId);

        if (!$isOwner && !$isReviewer) {
            abort(403, 'Kamu tidak punya akses ke diskusi dokumen ini.');
        }
    }

    public function addComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $document = Document::with('approvals')->findOrFail($id);
        $userId = Auth::id();

        $this->assertCanAccessDiscussion($document, $userId);

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

    /**
     * Ambil semua komentar suatu dokumen (dipakai untuk polling, biar pesan
     * baru dari orang lain muncul tanpa reload halaman).
     */
    public function fetchComments($id)
    {
        $document = Document::with('approvals')->findOrFail($id);
        $userId = Auth::id();

        $this->assertCanAccessDiscussion($document, $userId);

        $comments = $document->comments()
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(function ($c) use ($userId) {
                return [
                    'id' => $c->id,
                    'comment' => $c->comment,
                    'user_name' => $c->user->name ?? 'User',
                    'is_mine' => $c->user_id === $userId,
                    'created_at' => $c->created_at->diffForHumans(),
                ];
            });

        return response()->json(['comments' => $comments]);
    }

    /**
     * Tandai semua komentar orang lain di dokumen ini sebagai sudah dibaca
     * (dipanggil saat dokumen dibuka/di-preview).
     */
    public function markCommentsRead($id)
    {
        $document = Document::with('approvals')->findOrFail($id);
        $userId = Auth::id();

        $this->assertCanAccessDiscussion($document, $userId);

        $document->comments()
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Jumlah komentar belum dibaca per dokumen, untuk badge di sidebar list
     * dokumen. doc_ids dikirim sebagai query string dipisah koma.
     */
    public function unreadCommentCounts(Request $request)
    {
        $userId = Auth::id();

        $documentIds = collect(explode(',', (string) $request->query('doc_ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->values();

        if ($documentIds->isEmpty()) {
            return response()->json(['counts' => (object) []]);
        }

        // Batasi cuma ke dokumen yang user ini memang berhak akses (pemilik/reviewer)
        $accessibleIds = Document::whereIn('id', $documentIds)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhereHas('approvals', fn ($a) => $a->where('user_id', $userId));
            })
            ->pluck('id');

        $counts = Comment::whereIn('document_id', $accessibleIds)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->selectRaw('document_id, count(*) as total')
            ->groupBy('document_id')
            ->pluck('total', 'document_id');

        return response()->json(['counts' => $counts]);
    }

    public function download($id, string $type)
    {
        $document = Document::with(['approvals', 'latestVersion'])->findOrFail($id);

        $this->assertCanAccessDiscussion($document, Auth::id());

        $path = match ($type) {
            'signed' => $document->signed_file_path,
            'pdf'    => $document->latestVersion?->file_path,
            default  => null,
        };

        abort_if(!$path || !Storage::disk('public')->exists($path), 404);

        $name = Str::slug($document->title) . '.' . pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($path, $name);
    }
}