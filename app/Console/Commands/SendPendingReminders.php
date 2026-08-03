<?php

namespace App\Console\Commands;

use App\Models\DocumentApproval;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPendingReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Kirim WA reminder otomatis ke petinggi yang belum merespon, tiap 2 jam sekali per dokumen';

    public function handle()
    {
        $approvals = DocumentApproval::with(['document', 'user'])
            ->where('status', 'MENUNGGU')
            ->whereHas('document', function ($q) {
                $q->where('status', 'DITINJAU');
            })
            ->where(function ($q) {
                $q->whereNull('last_reminder_at')
                  ->orWhere('last_reminder_at', '<=', now()->subHours(2));
            })
            ->get();

        if ($approvals->isEmpty()) {
            $this->info('Tidak ada reminder yang perlu dikirim.');
            return;
        }

        foreach ($approvals as $approval) {
            $user = $approval->user;
            $document = $approval->document;

            if (!$user || !$user->phone) {
                continue;
            }

            $nomor = $this->formatNomorWA($user->phone);
            $link = route('app.petinggi.dashboard');
            $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');
            $lamaNganggur = $document->created_at->diffForHumans(null, true);

            $pesan = "Halo {$user->name},\n\nReminder otomatis: Anda memiliki pengajuan SK yang masih menunggu peninjauan:\n\n"
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

            $approval->update(['last_reminder_at' => now()]);

            $this->info("Reminder terkirim ke {$user->name} untuk dokumen {$document->document_number}");
        }
    }

    private function formatNomorWA($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }
}