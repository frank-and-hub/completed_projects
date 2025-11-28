<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\User;
use App\Notifications\ChallengeReminderNotification;
include app_path('helpers.php'); // Ensure this is included to use notifyUser function

class SendNewChallengeNotificationJob implements ShouldQueue
{
     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function handle()
    {
        // Send push via FCM
        notifyUser($this->item);

        // Send Laravel DB notification separately (optional redundancy)
        if (!empty($this->item['user_id'])) {
            $user = User::find($this->item['user_id']);

            if ($user && $user->notification_enabled) {
                if ($this->item['type'] === 'is_challenge') {
                    $user->notify(new ChallengeReminderNotification([
                        'title'   => $this->item['item'] ?? 'Challenge Reminder',
                        'message' => $this->item['message'] ?? 'Join the challenge now!',
                        'type'    => 'is_challenge',
                        'id'      => $this->item['id'] ?? null,
                    ]));
                }
            }
        }
    }
}
