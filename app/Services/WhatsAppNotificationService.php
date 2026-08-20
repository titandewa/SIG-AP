<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppNotificationService
{
    /**
     * Salam otomatis sesuai jam saat pesan dikirim.
     */
    public function salamWaktu()
    {
        $jam = (int) now()->format('H');

        if ($jam < 11) {
            return 'Pagi';
        } elseif ($jam < 15) {
            return 'Siang';
        } elseif ($jam < 19) {
            return 'Sore';
        }

        return 'Malam';
    }

    public function formatNomorWA($nomor)
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }

    private function kirim($nomor, $pesan)
    {
        Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target' => $nomor,
            'message' => $pesan,
        ]);
    }

    public function sendWaPengajuanBaru($user, $document)
    {
        if (!$user || !$user->phone) {
            return;
        }

        $nomor = $this->formatNomorWA($user->phone);
        $link = route('app.petinggi.dashboard');
        $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');
        $salam = $this->salamWaktu();
        $pengirim = $document->user->name ?? '-';

        $pesan = "Selamat {$salam} Bapak/Ibu {$user->name}\n\n"
            . "Untuk melengkapi administrasi, berikut saya lampirkan Surat Pengajuan yang perlu ditinjau :\n\n"
            . "Judul Surat : {$document->title}\n"
            . "Nomor Surat : {$document->document_number}\n"
            . "Tgl Pengajuan : {$tanggalUpload}\n"
            . "Diajukan oleh : {$pengirim}\n\n"
            . "Mohon segera ditinjau melalui link berikut, dan apabila tidak setuju mohon menyertakan alasan pada kolom komentar\n"
            . "{$link}\n\n"
            . "Terimakasih";

        $this->kirim($nomor, $pesan);
    }

    public function sendWaNotifAspri($user, $document, $statusPesan)
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

        $this->kirim($nomor, $pesan);
    }

    public function sendWaNotifDirut($dirut, $document)
    {
        if (!$dirut || !$dirut->phone) {
            return;
        }

        $nomor = $this->formatNomorWA($dirut->phone);
        $link = route('app.dirut.dashboard');
        $tanggalUpload = $document->created_at->translatedFormat('d F Y, H:i');
        $salam = $this->salamWaktu();
        $pengirim = $document->user->name ?? '-';

        $pesan = "Selamat {$salam} Bapak/Ibu {$dirut->name}\n\n"
            . "Untuk melengkapi administrasi, berikut saya lampirkan Surat Pengajuan yang perlu ditinjau :\n\n"
            . "Judul Surat : {$document->title}\n"
            . "Nomor Surat : {$document->document_number}\n"
            . "Tgl Pengajuan : {$tanggalUpload}\n"
            . "Diajukan oleh : {$pengirim}\n\n"
            . "Mohon segera ditinjau melalui link berikut, dan apabila tidak setuju mohon menyertakan alasan pada kolom komentar\n"
            . "{$link}\n\n"
            . "Terimakasih";

        $this->kirim($nomor, $pesan);
    }

    public function sendWaReminder($user, $document, $salam, $tanggalUpload, $link)
    {
        if (!$user || !$user->phone) {
            return false;
        }

        $nomor = $this->formatNomorWA($user->phone);
        $pengirim = $document->user->name ?? '-';

        $pesan = "Selamat {$salam} Bapak/Ibu {$user->name}\n\n"
            . "Untuk melengkapi administrasi, berikut saya lampirkan Surat Pengajuan yang perlu ditinjau :\n\n"
            . "Judul Surat : {$document->title}\n"
            . "Nomor Surat : {$document->document_number}\n"
            . "Tgl Pengajuan : {$tanggalUpload}\n"
            . "Diajukan oleh : {$pengirim}\n\n"
            . "Mohon segera ditinjau melalui link berikut, dan apabila tidak setuju mohon menyertakan alasan pada kolom komentar\n"
            . "{$link}\n\n"
            . "Terimakasih";

        $this->kirim($nomor, $pesan);

        return true;
    }
}