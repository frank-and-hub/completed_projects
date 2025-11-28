<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

require_once app_path('helpers.php');

class SendWorkoutNotifications extends Command
{
    protected $signature = 'app:send-workout-notifications';
    protected $description = 'Send daily workout reminders at 6 AM IST';

    public function handle()
    {
        $users = User::whereNotNull('device_id')
            ->whereHas('activeWorkoutPlan') // must have a workout plan
            ->whereHas('subscriptions', function($q) {
                $q->where('expires_at', '>', now());
                   // ->where('expires_at', '>', now());
            })
            ->whereNull('deleted_at') // must not be soft deleted
            ->with(['activeWorkoutPlan'])
            ->get();

        $eligibleUsers = [];

        foreach ($users as $user) {
            $workoutPlan = $user->activeWorkoutPlan;

            // skip if no plan
            if (!$workoutPlan) {
                continue;
            }

            // skip if user doesn't have a subscription
            if (!$user->subscriptions()->exists()) {
                continue;
            }

            // skip if today is rest day
            if ($workoutPlan->is_rest_day) {
                continue;
            }

            // set default title/message: user DID workout yesterday
            $title = "Time to Show Up for You 💪";
            $message = "Your session’s waiting! Stay on track, stay strong — one rep closer to your goals.";

            // if workout is missed or partially completed
            if ($workoutPlan->is_completed === 0 && $workoutPlan->is_completed === 1) {
                $title = "Don’t Break the Momentum";
                $message = "You missed your last session — let’s bounce back today. You’ve got this!";
            }

            // assign title/message to user for later use
            $user->notification_title = $title;
            $user->notification_message = $message;

            $eligibleUsers[] = $user;
        }

        if (empty($eligibleUsers)) {
            Log::info("No eligible users found for workout notifications.");
            return Command::SUCCESS;
        }

        foreach ($eligibleUsers as $user) {
            notifyUser([
                'deviceToken' => $user->device_id,
                'type'        => 'is_workout',
                'title'       => $user->notification_title,
                'message'     => $user->notification_message,
                'readcount'   => 0,
                'item'        => "Workout Reminder",
                'id'          => $user->id,
                'user_id'     => $user->id,
            ]);
        }

        Log::info("Workout notifications sent to " . count($eligibleUsers) . " users.");
        return Command::SUCCESS;
    }
}
