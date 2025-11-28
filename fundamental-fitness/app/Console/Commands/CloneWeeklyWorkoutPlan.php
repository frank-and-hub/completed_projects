<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserWorkout;
use App\Traits\UserWorkoutCloner;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CloneWeeklyWorkoutPlan extends Command
{
    use UserWorkoutCloner;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clone-weekly-workout-plan {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $maxMeso = get_meso_cycle()->max('id');
        $maxWeek = get_weeks()->max('id');

        $userId = (int) $this->argument('user_id');

        $query = User::whereHas('work_out_frequency')
            ->whereNotNull('meso_start_date')
            ->whereIsSubscribe(true)
            ->whereNotNull('workout_frequency');

        if ($userId) {
            $query->where('id', $userId);
        }

        $query->chunk(10, function ($users) use ($today, $maxMeso, $maxWeek) {
            foreach ($users as $k => $user) {
                Log::channel('cron')->info("User Data added for clone-weekly-workout-plan : $user->id");
                try {
                    $maxDays = optional($user->work_out_frequency)->days_in_week ?? 6;
                    $startDate = Carbon::parse($user->meso_start_date);
                    $daysSinceStart = $startDate->diffInDays($today);
                    $daysPerWeek = $user->work_out_frequency->id;
                    $weekNumber = intdiv($daysSinceStart, 7) + 1;
                    $dayNumber  = ($daysSinceStart % 7) + 1;
                    $mesoId = intdiv($weekNumber - 1, 4) + 1;
                    $weekId = (($weekNumber - 1) % 4) + 1;
                    $isLastDay = ($mesoId >= $maxMeso) && ($weekId >= $maxWeek) && ($dayNumber >= $maxDays);

                    if ($isLastDay && !get_meso_cycle()->where('id', $mesoId)->value('name') && !get_weeks()->where('id', $weekId)->value('name')) {
                        $this->info('Meso cycle already completed ! A');
                        continue;
                    } elseif (!get_meso_cycle()->where('id', $mesoId)->value('name')) {
                        $this->info('Meso cycle already completed ! B' .  $mesoId);
                        continue;
                    }

                    $exists = UserWorkout::where('user_id', $user->id)
                        ->where('meso_id', $mesoId)
                        ->where('week_id', $weekId)
                        ->exists();

                    if ($exists) {
                        $this->info("Workout already exists for user {$user->id}, meso {$mesoId}, week {$weekId}, day {$dayNumber}");
                        continue;
                    } else {
                        $this->cloneWorkoutsForUser($user->id, $mesoId, $weekId, $daysPerWeek);
                        $firstWorkout = UserWorkout::where('user_id', $user->id)
                            ->where('meso_id', $mesoId)
                            ->where('week_id', $weekId)
                            ->where('day_id', 1)
                            ->where('workout_frequency_id', $daysPerWeek)
                            ->whereStatus('1')
                            ->orderBy('exercise_id')
                            ->first();

                        if ($firstWorkout?->id) {
                            $firstWorkout->update(['status' => '0']);
                        }

                        $workoutsQuery = UserWorkout::where('user_id', $user->id)->whereNull('execution_date');

                        $count = 0;
                        $workoutsQuery->chunkById(100, function ($workouts) use ($startDate, $user, &$count) {
                            foreach ($workouts as $uw) {
                                $overallWeekNumber = (($uw->meso_id - 1) * 4) + $uw->week_id; // 1-based
                                $dayOffset = (($overallWeekNumber - 1) * 7) + ($uw->day_id - 1);
                                $executionDate = $startDate->copy()->addDays($dayOffset)->toDateString();

                                $uw->execution_date = $executionDate;
                                $uw->save();
                                $count++;
                            }
                        });
                        Log::channel('cron')->info("Generated workout for user {$user->id}, meso {$mesoId}, week {$weekId}, day {$dayNumber}");
                        $this->info("Generated workout for user {$user->id}, meso {$mesoId}, week {$weekId}, day {$dayNumber}");
                    }
                } catch (\Throwable $e) {
                    Log::channel('cron')->error('Error queuing user {$user->id}: ' . $e->getMessage());
                    continue;
                }
            }
            return SymfonyCommand::SUCCESS;
        });
    }
}
