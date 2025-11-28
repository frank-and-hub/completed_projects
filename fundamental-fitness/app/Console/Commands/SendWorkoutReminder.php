<?php

namespace App\Console\Commands;

use App\Models\UserWorkout;
use App\Traits\Common_trait;
use Illuminate\Console\Command;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWorkoutReminder extends Command
{
    use Common_trait;
    protected $signature = 'notifications:workout-reminder {user_id?}';
    protected $description = 'Send workout reminder notification to users';

    public function handle(NotificationService $notifications): int
    {
        $userId = (int) $this->argument('user_id');
        $todayDate = date('Y-m-d');
        $query = User::query()
            ->when($userId, fn($q) => $q->where('id', $userId))
            ->where('notifications_enabled', true)
            ->whereNotNull('device_id')
            ->whereHas('user_workouts', function ($q) use($todayDate) {
                $q->whereDate('execution_date', $todayDate);
            });
        $query->chunk(10, function ($users) use ($notifications, $todayDate) {
            foreach ($users as $user) {
                Log::channel('cron')->info("notifications:workout-reminder for user : $user->id");
                $windowStartDate = Carbon::parse($todayDate)->startOfDay()->format('Y-m-d');
                $windowEndDate   = Carbon::parse($todayDate)->endOfDay()->format('Y-m-d');
                $scheduledButNotCompleted = UserWorkout::whereHas('sets', function ($q) {
                    $q->whereNull('completed_at');
                })
                    ->where('user_id', $user->id)
                    ->whereBetween(DB::raw('DATE(execution_date)'), [$windowStartDate, $windowEndDate])
                ->exists();
                if ($this->is_eligible_for_reset($user)) {
                    $this->info("User ID {$user->id} has workouts scheduled or completed for today. Skipping notification.");
                    continue;
                } else if (!$scheduledButNotCompleted) {
                    $this->info("User ID {$user->id} has already completed for today. Skipping notification.");
                    continue;
                }

                $notifications->sendWorkoutReminder($user->id);
                Log::channel('cron')->info('Workout reminder sent to user ID: ' . $user->id);
                $this->info('Workout reminder sent to user ID: ' . $user->id);
            }
        });
        return self::SUCCESS;
    }
}
