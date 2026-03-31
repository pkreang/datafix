<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineNotifyChannel
{
    private const ENDPOINT = 'https://notify-api.line.me/api/notify';

    public function send(object $notifiable, Notification $notification): void
    {
        $token = $notifiable->line_notify_token ?? null;

        if (! $token) {
            return;
        }

        $message = method_exists($notification, 'toLineNotify')
            ? $notification->toLineNotify($notifiable)
            : $this->fallbackMessage($notification, $notifiable);

        if (! $message) {
            return;
        }

        try {
            $response = Http::withToken($token)
                ->asForm()
                ->post(self::ENDPOINT, ['message' => $message]);

            if ($response->failed()) {
                Log::warning('LINE Notify failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'user_id' => $notifiable->id ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('LINE Notify error', [
                'message' => $e->getMessage(),
                'user_id' => $notifiable->id ?? null,
            ]);
        }
    }

    private function fallbackMessage(Notification $notification, object $notifiable): ?string
    {
        $data = method_exists($notification, 'toArray')
            ? $notification->toArray($notifiable)
            : null;

        if (! $data) {
            return null;
        }

        $parts = ["\n" . ($data['title'] ?? '')];

        if ($data['body'] ?? null) {
            $parts[] = $data['body'];
        }

        if ($data['url'] ?? null) {
            $parts[] = url($data['url']);
        }

        return implode("\n", $parts);
    }
}
