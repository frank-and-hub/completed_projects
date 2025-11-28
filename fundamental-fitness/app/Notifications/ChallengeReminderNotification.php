<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;

class ChallengeReminderNotification extends Notification
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $payload = [
            'title' => $this->data['title'] ?? 'New Challenge',
            'message' => $this->data['message'] ?? 'A fresh fitness challenge just dropped. Accept it, crush it, brag later. Tap to check it out.',
            'type' => $this->data['type'] ?? 'is_challenge',
             'id'      => $this->data['id'] ?? null,
        ];

        // Log::info('ChallengeNotification toArray:', $payload); 

        return $payload;
    }
}
