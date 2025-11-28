<?php

namespace App\Traits;

use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserWorkout;
use App\Models\Workout;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait Common_trait
{
    public function create_unique_slug($string = '', $table = '', $field = 'slug', $col_name = null, $old_slug = null)
    {
        $slug = Str::of($string)->slug('-');
        $slug = strtolower($slug);

        $i = 0;
        $params = array();
        $params[$field] = $slug;
        if ($col_name) {
            $params["$col_name"] = "<> $old_slug";
        }

        while (DB::table($table)->where($params)->count()) {
            if (!preg_match('/-{1}[0-9]+$/', $slug)) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace('/[0-9]+$/', ++$i, $slug);
            }
            $params[$field] = $slug;
        }
        return $slug;
    }

    public function deleteFile($filePath)
    {
        if ($filePath) {
            $disk = config('constants.file_upload_location');

            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
                return true;
            }
        }

        return false;
    }

    public function sendEmail($email = '', Mailable $mailable): bool
    {
        try {
            Mail::to($email)->send($mailable);

            // Log::info("OTP email sent successfully to: " . $email);
            return true;
        } catch (\Exception $e) {
            // Log failure
            Log::error("Failed to send OTP email to: " . $email . ". Error: " . $e->getMessage());

            return false;
        }
    }

    public function file_upload($file, $folder)
    {
        $path = storage_path('app/public/uploads/' . $folder);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            chmod($path, 0755);
        }

        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move($path, $fileName);

        return 'storage/uploads/' . $folder . '/' . $fileName;
    }


    public function applySearch($query, $search, array $columns = ['name'])
    {
        if ($search) {
            $query->whereAny($columns, 'like', '%' . $search . '%');
        }

        return $query;
    }


    /**
     * Calculate weekly workout completion rate for a user.
     *
     * @param  int  $userId
     * @param  string  $mode  ('calendar' or 'program')
     * @return float
     */
    public function getTotalWeeklyWorkoutCompletionRate(): float
    {
        $currentWeekId = UserWorkout::query()->max('week_id');
        if (!$currentWeekId) {
            return 0;
        }
        $assigned = UserWorkout::query()
            ->where('week_id', $currentWeekId)
            ->count();
        $completed = UserProgress::query()
            ->where('week_id', $currentWeekId)
            ->where('status', 2)->distinct(['user_id', 'exercise_id'])->count();
        return completion_rate($assigned, $completed);
    }

    public function unlockNextDayExercises($currentUserId, $mesoId, $weekId, $dayId)
    {
        $dayCompleted = is_day_completed($currentUserId, $dayId, $weekId, $mesoId);

        if ($dayCompleted) {
            $nextDayId = $dayId + 1;

            // Unlock next day if it exists in this week
            $nextDayExists = UserWorkout::whereUserId($currentUserId)
                ->whereWeekId($weekId)
                ->whereMesoId($mesoId)
                ->whereDayId($nextDayId)
                ->exists();

            if ($nextDayExists) {
                UserWorkout::whereUserId($currentUserId)
                    ->whereWeekId($weekId)
                    ->whereMesoId($mesoId)
                    ->whereDayId($nextDayId)
                    ->update(['status' => 0]);
            } else {
                // If no next day, maybe unlock next week/day 1?
                $nextWeekId = $weekId + 1;
                $firstDayExists = UserWorkout::whereUserId($currentUserId)
                    ->whereWeekId($nextWeekId)
                    ->whereMesoId($mesoId)
                    ->whereDayId(1)
                    ->exists();

                if ($firstDayExists) {
                    UserWorkout::whereUserId($currentUserId)
                        ->whereWeekId($nextWeekId)
                        ->whereMesoId($mesoId)
                        ->whereDayId(1)
                        ->update(['status' => 0]);
                } else {
                    // If no next week, maybe meso next meso/week/day 1?
                    $nextWeekId = $weekId + 1;
                    $firstDayExists = UserWorkout::whereUserId($currentUserId)
                        ->whereMesoId($mesoId)
                        ->whereDayId(1)
                        ->exists();

                    if ($firstDayExists) {
                        UserWorkout::whereUserId($currentUserId)
                            ->whereMesoId($mesoId)
                            ->whereDayId(1)
                            ->update(['status' => 0]);
                    }
                }
            }
            Artisan::call('app:add-workout-plan-command');
        }
    }

    /**
     * Determine if a user is eligible to reset their workout profile.
     */
    public function isEligibleForReset(User $user): bool
    {
        $activePointer = get_active_pointer($user->id, $user->meso_start_date);
        $status = false;
        $latestProgress = UserWorkout::where('user_id', $user->id)
            ->selectRaw('MAX(meso_id) as max_meso, MAX(week_id) as max_week, MAX(day_id) as max_day')
            ->first();
        if (!$latestProgress) {
            return false;
        }
        if ($latestProgress->max_meso == get_meso_cycle()->max('id') && $latestProgress->max_week == get_weeks()->max('id')) {
            // return false;
            $workoutFrequency = optional($user->work_out_frequency)->days_in_week ?? 6;
            $hasIncompleteWeights = UserProgress::where('user_id', $user->id)
                ->whereNull('completed_at')
                ->exists();
            $hasPendingWorkouts = UserProgress::where('user_id', $user->id)
                ->whereNotIn('status',  [2])
                ->exists();
            $hasCompletedAllWorkouts =
                ($activePointer['meso_id'] ?? 0) >= ($latestProgress->max_meso ? (int) $latestProgress->max_meso : 0) &&
                ($activePointer['week_id'] ?? 0) >= ($latestProgress->max_week ?? 0) &&
                ($activePointer['day_id'] ?? 0) >= ($workoutFrequency);
            // return $hasCompletedAllWorkouts && !$hasPendingWorkouts && !$hasIncompleteWeights;
            if ($hasCompletedAllWorkouts) {
                if (!$hasPendingWorkouts) {
                    if (!$hasIncompleteWeights) {
                        $status = true;
                    }
                }
            }
        }
        return $status;
    }

    public function is_eligible_for_reset(User $user)
    {
        // $catchData = Cache::get('is_eligible_for_reset_' . $user->id);
        // if (!$catchData) {
        $data = $this->isEligibleForReset($user);
        // Cache::put('is_eligible_for_reset_' . $user->id, $data, now()->addDay());
        $catchData = $data;
        // }
        return  $catchData;
    }


    public  function get_workout_unlock_status($user, $mId, $wId, $dId)
    {
        if ($mId == 1 && $wId == 1 && $dId == 1) {
            return [
                'meso_unlocked' => true,
                'week_unlocked' => true,
                'day_unlocked'  => true,
            ];
        }
        $mesoStartDate = $user->meso_start_date;
        $today = Carbon::today();
        $startDate = Carbon::parse($mesoStartDate);
        $daysSinceStart = $startDate->diffInDays($today);
        $weekNumber = intdiv($daysSinceStart, 7) + 1;
        $dayNumber  = ($daysSinceStart % 7) + 1;
        $currentMesoId = intdiv($weekNumber - 1, 4) + 1;
        $currentWeekId = (($weekNumber - 1) % 4) + 1;
        // Log::info("\n mesoId: $mId, weekId: $wId, dayId: $dId   \n startDate: $startDate , weekNumber: $weekNumber, dayNumber: $dayNumber , currentMesoId: $currentMesoId");
        $response = [
            'meso_unlocked' => false,
            'week_unlocked' => false,
            'day_unlocked'  => false,
        ];
        if ($currentMesoId > $mId) {
            $response = [
                'meso_unlocked' => true,
                'week_unlocked' => true,
                'day_unlocked'  => true,
            ];
        } elseif ($currentMesoId == $mId) {
            $response['meso_unlocked'] = true;
            if ($currentWeekId > $wId) {
                $response['week_unlocked'] = true;
                $response['day_unlocked']  = true;
            } elseif ($currentWeekId == $wId) {
                $response['week_unlocked'] = true;
                if ($dayNumber >= $dId) {
                    $response['day_unlocked'] = true;
                }
            }
        }
        return $response;
    }

    public function get_all_workout_data($currentUser)
    {
        $workoutFrequency = $currentUser->work_out_frequency->id;
        $activePointer = get_active_pointer($currentUser->id, $currentUser->meso_start_date);
        $mId = $activePointer['meso_id'];
        $wId = $activePointer['week_id'];
        $admin = Workout::query()
            ->when($mId, fn($q) => $q->where('meso_id', $mId))
            ->when($wId, fn($q) => $q->where('week_id', '>', $wId))
            // ->when($dId, fn($q) => $q->where('day_id', '!=', $dId))
            ->when($workoutFrequency, fn($q) => $q->where('workout_frequency_id', $workoutFrequency))
            ->get()
            ->groupBy('week_id')
            ->sortKeys()
            ->map(function ($weekGroup, $wId) {
                return [
                    'id'   => (int) $wId,
                    'name' => "Week $wId",
                    'status' => false,
                    'is_completed' => false,
                    'days' => $weekGroup->groupBy('day_id')->sortKeys()->map(function ($dayGroup, $dId) {
                        return [
                            'id' => (int) $dId,
                            'status' => false,
                            'is_completed' => false,
                            'exercise_count' => 0,
                        ];
                    })->values()->toArray()
                ];
            })->values();
        return $admin;
    }

    public function calculateGroupedData($query, $mesoNames, $weekNames, $currentUser, $weeksCount)
    {
        return $query->groupBy('meso_id')->sortKeys()->map(function ($mesoGroup, $mId) use ($mesoNames, $weekNames, $currentUser, $weeksCount) {
            // get unlock status for meso (which is assigned to user)
            $get_workout_unlock_status = $this->get_workout_unlock_status($currentUser, $mId, $wId = 1, $dId = 1);
            //get all workout data for locked weeks (which is not assigned to user but exists in admin workouts)
            $lock_weeks = $this->get_all_workout_data($currentUser);
            $unlock_weeks = $mesoGroup->groupBy('week_id')->sortKeys()->map(function ($weekGroup, $wId) use ($weekNames, $mId, $currentUser) {
                $get_workout_unlock_status = $this->get_workout_unlock_status($currentUser, $mId, $wId, $dId = 1);
                return [
                    'id' => (int) $wId,
                    'name' => $weekNames[$wId] ?? null,
                    'status' =>  $get_workout_unlock_status['week_unlocked'],
                    'is_completed' => is_week_completed($currentUser->id, $wId, $mId,),
                    'days' => $weekGroup->groupBy('day_id')->sortKeys()->map(function ($dayGroup, $dId) use ($wId, $mId, $currentUser) {
                        $get_workout_unlock_status = $this->get_workout_unlock_status($currentUser, $mId, $wId, $dId);
                        return [
                            'id' => (int) $dId,
                            'status' => $get_workout_unlock_status['day_unlocked'],
                            'is_completed' => is_day_completed($currentUser->id, $dId, $wId, $mId,),
                            'exercise_count' => $dayGroup->sum('exercise_count')
                        ];
                    })->values()
                ];
            })->values();

            return [
                'meso_id' => (int) $mId,
                'name' => $mesoNames[$mId] ?? null,
                'status' => $get_workout_unlock_status['meso_unlocked'],
                'is_completed' => is_meso_completed($currentUser->id, $mId),
                'weeks_count' => (int) $weeksCount,
                'weeks' => [...$unlock_weeks, ...$lock_weeks]
            ];
        })->values();
    }
}
