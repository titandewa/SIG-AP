<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Contract\Messaging;
use App\Models\User;
use App\Models\NotificationHistory;

class FcmService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Kirim notifikasi ke satu user (semua device/token miliknya).
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        $tokens = $user->fcmTokens()->pluck('token')->toArray();

        $this->recordHistory($user, $title, $body, 'browser', 'sent', $data);

        if (empty($tokens)) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Kirim notifikasi ke banyak user sekaligus.
     */
    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Kirim notifikasi langsung ke daftar token (data-only, biar gak dobel notif).
     */
    protected function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        // Semua info dimasukkan ke 'data', TIDAK pakai withNotification(),
        // biar browser tidak auto-tampilkan notif sendiri (mencegah notif dobel).
        $payload = array_merge($data, [
            'title' => $title,
            'body' => $body,
        ]);

        $message = CloudMessage::new()->withData($payload);

        foreach ($tokens as $token) {
            try {
                $this->messaging->send($message->withToken($token));
            } catch (\Throwable $e) {
                \App\Models\FcmToken::where('token', $token)->delete();
            }
        }
    }

    protected function recordHistory(User $user, string $title, string $body, string $channel, string $status, array $data = []): void
    {
        NotificationHistory::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $data['type'] ?? null,
            'channel' => $channel,
            'status' => $status,
            'data' => $data,
        ]);
    }
}