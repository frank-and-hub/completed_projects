<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Carbon\Carbon;
use App\Models\FitnessChallenge;
use App\Jobs\SendNewChallengeNotificationJob;

require_once app_path('helpers.php');

class SendChallengesNotifications extends Command
{
    protected $signature = 'app:send-challenges-notifications';
    protected $description = 'Send challenge notifications to all users for newly created challenges';

    public function handle()
    {
        $this->info('Sending challenge notifications to users...');
        Log::info('Starting challenge notification job');

        $recentlyCreated = Carbon::now()->subMinute();

        $challenges = FitnessChallenge::where('created_at', '>=', $recentlyCreated)->get();


        if ($challenges->isEmpty()) {
            Log::info('No new challenges found');
            $this->info('No new challenges to notify.');
            return Command::SUCCESS;
        }

        //Fetch users with device token (for push notification)
        $users = User::select('id', 'device_id', 'is_profile_completed')
            ->whereNotNull('device_id')
            ->whereNull('deleted_at')
            ->orWhere('is_profile_completed', '!=', 0)
            ->get();

        if ($users->isEmpty()) {
            Log::warning('No eligible users found with device_id and is_profile_completed = 1');
        } else {
            Log::info("Found {$users->count()} users to notify");
        }


        foreach ($challenges as $challenge) {
            foreach ($users as $user) {
                $message = "{$challenge->title}A fresh fitness challenge just dropped. Accept it, crush it, brag later. Tap to check it out.";

                // Push notification
                notifyUser([
                    'deviceToken' => $user->device_id,
                    'type'        => 'is_challenge',
                    'title'       => 'New Challenge🔥',
                    'message'     => $message,
                    'item'        => "New Challenge🔥",
                    'id'          => $challenge->id,
                    'user_id'     => $user->id,
                ]);

                // (Optional) Dispatch to DB/Email/Queue job
                SendNewChallengeNotificationJob::dispatch([
                    'user_id' => $user->id,
                    'title'   => 'New Challenge🔥',
                    'message' => $message,
                    'type'    => 'is_challenge',
                    'item_id' => $challenge->id,
                ]);

                Log::info("Challenge alert sent to user {$user->id} for challenge {$challenge->id}");
            }
        }

        $this->info('Challenge notifications sent successfully!');
        return Command::SUCCESS;
    }
}
