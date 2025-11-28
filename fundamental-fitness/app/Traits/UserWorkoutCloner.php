<?php

namespace App\Traits;

use App\Models\Exercise;
use App\Models\UserProgress;
use App\Models\UserWorkout;
use App\Models\Workout;

trait UserWorkoutCloner
{
    public static  function cloneWorkoutsForUser($userId, $mesoId, $weekId, $workoutFrequencyId)
    {
        $workouts = Workout::with('sets')
            ->where('meso_id', $mesoId)
            ->where('workout_frequency_id', $workoutFrequencyId)
            ->where('week_id', $weekId)
            ->get();

        if ($workouts->isEmpty()) {
            return response()->json(['message' => 'No workouts found for given filters'], 404);
        }

        $traningMaxPercentage = 0.7; // 70%
        foreach ($workouts as $key => $workout) {
            // $userWorkoutData = [
            $userworkout = UserWorkout::updateOrCreate([
                'user_id'              => $userId,
                'workout_frequency_id' => $workout->workout_frequency_id,
                'meso_id'              => $workout->meso_id,
                'day_id'               => $workout->day_id,
                'week_id'              => $workout->week_id,
                'exercise_id'          => $workout->exercise_id,
            ], [
                'level'                => $workout->level,
                'image'                => $workout->image,
                'video'                => $workout->video,
                'gif'                  => $workout->gif,
                'description'          => $workout->description,
                'status'               => '1',
            ]);
            // $userworkout = UserWorkout::create($userWorkoutData);
            $workoutSetData = [];
            foreach ($workout->sets as $k => $set) {
                $weight = $set->weight ?? 0;
                $rpe    = $set->rpe ?? 1;
                $set_number = $set->set_number;

                $exerciseId = Exercise::whereIn('name', [
                    'Bench Press',
                    'Squat',
                    'Deadlift'
                ])
                    ->pluck('id')
                    ->toArray();

                if (in_array($userworkout->exercise_id, $exerciseId)) {
                    $lastStats = UserProgress::where('user_id', $userId)
                        ->where('exercise_id', $userworkout->exercise_id)
                        ->where('meso_id', $mesoId - 1)
                        ->where('day_id', $userworkout->day_id)
                        ->whereNotNull('weight')
                        ->orderByDesc('created_at')
                        // ->limit(5) // look at last 5 sessions, adjust if needed
                        ->get();

                    $weights = [];
                    $rpes    = [];

                    foreach ($lastStats as $stat) {
                        // Decode weight (could be JSON or plain number)
                        $decodedWeights = json_decode($stat->weight, true);

                        if (is_array($decodedWeights)) {
                            // if JSON array/object, collect values
                            $weights = array_merge($weights, array_values($decodedWeights));
                        } elseif (is_numeric($stat->weight)) {
                            // fallback if stored as number
                            $weights[] = $stat->weight;
                        }

                        if (!empty($stat->rpe)) {
                            $rpes[] = $stat->rpe;
                        }
                    }

                    if (!empty($weights)) {
                        $weight = round(array_sum($weights) / count($weights) * $traningMaxPercentage, 2);
                    }

                    if (!empty($rpes)) {
                        $rpe = round(array_sum($rpes) / count($rpes), 1);
                    }
                }

                $defaultWeight = json_encode(array_fill(0, $set_number, $weight));
                if ($workout->exercise_id == get_running_id()) {
                    $defaultWeight  = json_encode([0]);
                }

                $workoutSetData[] =  [
                    'user_id'     => $userId,
                    'meso_id'     => $userworkout->meso_id,
                    'week_id'     => $userworkout->week_id,
                    'day_id'      => $userworkout->day_id,
                    'exercise_id' => $userworkout->exercise_id,
                    'set_id'      => $set->id,
                    'weight'      => $defaultWeight,
                    'reps'        => $set->reps ?? 0,
                    'rpe'         => $rpe,
                    'status'      => 0,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                    'user_workout_id' => $userworkout->id
                ];
            }
            // UserProgress::insert($workoutSetData);
            foreach ($workoutSetData as $data) {
                UserProgress::updateOrCreate(
                    [
                        'user_id' => $data['user_id'],
                        'meso_id' => $data['meso_id'],
                        'week_id' => $data['week_id'],
                        'day_id' => $data['day_id'],
                        'exercise_id' => $data['exercise_id'],
                        'set_id' => $data['set_id'],
                        'user_workout_id' => $data['user_workout_id'],
                        'status' => $data['status'],
                    ],
                    [
                        'weight' => $data['weight'],
                        'reps' => $data['reps'],
                        'rpe' => $data['rpe'],
                        'updated_at' => now(),
                    ]
                );
            }
        }
        return [
            'message' => 'Workouts cloned successfully',
        ];
    }
}
