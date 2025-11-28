<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserWorkout;
use App\Models\UserProgress;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendProgressUpdate extends Command
{
    protected $signature = 'notifications:progress-update {user_id?}';
    protected $description = 'Send weekly progress update to users based on meso_start_date windows';

    public function handle(NotificationService $notifications): int
    {
        $userId = (int) $this->argument('user_id');
        $query = User::query()
            ->select('id', 'meso_start_date')
            ->where('notifications_enabled', true)
            ->whereNotNull('device_id')
            ->whereNotNull('meso_start_date');
        if ($userId) {
            $query->where('id', $userId);
        }
        $todayDate = date('Y-m-d');
        $query->chunk(10, function ($users) use ($todayDate, $notifications) {
            foreach ($users as $user) {
                Log::channel('cron')->info("notifications:progress-update for user : $user->id");
                try {
                $startDate = date('Y-m-d', strtotime($user->meso_start_date));
                $days = floor((strtotime($todayDate) - strtotime($startDate)) / 86400);
                // if ($days < 7 || ($days % 7) !== 0) {
                //     continue; // only send on 7-day boundaries after the first week
                // }
                $weeksElapsed = intdiv($days, 7);
                $windowStartDate = date('Y-m-d', strtotime("+" . (($weeksElapsed) * 7) . " days", strtotime($startDate)));
                $windowEndDate = date('Y-m-d', strtotime("+" . ($weeksElapsed * 7) . " days", strtotime($startDate)));
                    $scheduledDays = UserWorkout::where('user_id', $user->id)
                        ->whereBetween(DB::raw('DATE(execution_date)'), [$windowStartDate, $windowEndDate])
                        ->distinct()
                        ->pluck('day_id')
                        ->count();
                    if ($scheduledDays === 0) {
                        $this->info("scheduled days count is 0 for user ID - $user->id");
                        continue;
                    }
                    $completedDays = UserProgress::where('user_id', $user->id)
                        ->whereBetween(DB::raw('DATE(completed_at)'), [$windowStartDate, $windowEndDate])
                        ->whereIn('status', [2])
                        ->distinct()
                        ->pluck('day_id')
                        ->count();
                $percent = (int) round(($completedDays / max(1, $scheduledDays)) * 100);
                $notifications->sendProgressUpdate($user->id, $percent);
                Log::channel('cron')->info("Notification is sent for user id - $user->id");
                $this->info("Notification is sent for user id - $user->id");
                } catch (Exception $e) {
                    Log::channel('cron')->error("Error in progress-update notifications cron: " . $e->getMessage());
                }
            }
        });
        $this->info('Progress updates evaluated and dispatched for week windows.');
        return self::SUCCESS;
    }
}
