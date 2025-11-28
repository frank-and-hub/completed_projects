<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Models\User;
use App\Models\Exercise;
use App\Models\UserProgress;
use App\Models\UserChallengeProgress;
use App\Models\BodyType;
use App\Models\FitnessChallengeExercise;
use App\Models\UserExerciseProgressWorkout;
use App\Models\UserExerciseProgressChallenge;
use App\Models\FitnessChallenge;
use App\Models\FitnessChallengeWeekDay;
use App\Models\SubscriptionPackages;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChallengeController extends BaseApiController
{
    public function getChallenges()
    {
        try {
            $user_id = Auth::id();

            $challenges = FitnessChallenge::whereNull('deleted_at')
                ->get(['id', 'challenge_name', 'goal', 'duration_weeks', 'image', 'description']);


            if ($challenges->isEmpty()) {
                return $this->sendError('No challenges found.', [], 404);
            }

            // Get all user progress
            $userProgress = UserChallengeProgress::with('exerciseProgress')
                ->where('user_id', $user_id)
                ->whereIn('challenge_id', $challenges->pluck('id'))
                ->get()
                ->groupBy(['challenge_id', 'week_id', 'day_id']);


            $formattedChallenges = $challenges->map(function ($challenge) use ($userProgress) {
                $challenge->load(['weekDays.exercises.exercise.bodyType']);

                $challengeProgress = $userProgress->get($challenge->id) ?? collect();

                // Determine the active week (first one with progress or default to week 1)
                $activeWeek = $challengeProgress->keys()->first() ?? 1;

                $weeks = $challenge->weekDays
                    ->groupBy('week')
                    ->map(function ($days, $weekNumber) use ($challengeProgress, $activeWeek) {
                        $weekProgress = $challengeProgress->get($weekNumber) ?? collect();
                        $weekIsActive = $weekNumber == 1 ? 1 : ($days->contains(fn($day) => $day->is_active == 1) ? 1 : 0);



                        return [
                            'week_id' => $weekNumber,
                            'week_number' => $weekNumber,
                            'is_active' => $weekNumber == 1 ? 1 : ($days->contains(fn($day) => $day->is_active == 1) ? 1 : 0),
                            'days' => $days->map(function ($day) use ($weekNumber, $weekProgress, $activeWeek) {
                                $dayProgress = $weekProgress->get($day->id);
                                $exerciseStatuses = $dayProgress ? $dayProgress->first()->exerciseProgress->pluck('is_completed', 'exercise_id')->toArray() : [];
                                $progressRecord = UserChallengeProgress::where('user_id', Auth::id())->where('challenge_id', $day->fitness_challenge_id)->first();
                                $startDate = $progressRecord?->start_date;
// pree($progressRecord,'s');


                                return [
                                    'day_id' => $day->day_number,
                                    'is_rest_day' => $day->is_rest_day ? 1 : 0,
                                    'is_active' => (function () use ($startDate, $weekNumber, $day) {
                                        if (!$startDate) return 0;

                                        $start = Carbon::parse($startDate);
                                        $today = Carbon::today();
                                        $dayOffset = (($weekNumber - 1) * 7) + ($day->day_number - 1);
                                        $dayDate = $start->copy()->addDays($dayOffset);

                                        if ($dayDate->lt($today)) {
                                            return 2; // Past
                                        } elseif ($dayDate->isToday()) {
                                            return 1; // Today
                                        } else {
                                            return 0; // Future
                                        }
                                    })(),
                                    'status' => (function () use ($day) {
                                        if ($day->is_rest_day) return 2;

                                        $exercises = $day->exercises;
                                        $totalExercises = $exercises->count();
                                        if ($totalExercises === 0) return 0;

                                        $progressId = UserChallengeProgress::where('user_id', Auth::id())
                                            ->where('challenge_id', $day->fitness_challenge_id)
                                            ->where('week_id', $day->week)
                                            ->where('day_id', $day->day_number)
                                            ->value('id');

                                        if (!$progressId) {
                                            return 0;
                                        }

                                        $completedExercises = $exercises->filter(function ($exercise) use ($progressId) {
                                            $query = UserExerciseProgressChallenge::where('exercise_id', $exercise->exercise_id)
                                                ->where('user_id', Auth::id());

                                            if ($progressId) {
                                                $query->where('progress_id', $progressId);
                                            }



                                            return $query->latest()->value('is_completed') == 1;
                                        })->count();

                                        if ($completedExercises === 0) return 0;
                                        if ($completedExercises === $totalExercises) return 2;
                                        return 1;
                                    })(),

                                    'exercises' => $day->is_rest_day ? [] : (function () use ($day) {
                                        $progressId = UserChallengeProgress::where('user_id', Auth::id())
                                            ->where('challenge_id', $day->fitness_challenge_id)
                                            ->where('week_id', $day->week)
                                            ->where('day_id', $day->day_number)
                                            ->value('id');


                                        return $day->exercises->map(function ($exercise) use ($progressId) {
                                            $query = UserExerciseProgressChallenge::where('exercise_id', $exercise->exercise_id)
                                                ->where('user_id', Auth::id());

                                            if ($progressId) {
                                                $query->where('progress_id', $progressId);
                                            }

                                            if ($progressId) {
                                                $status = $query->latest()->value('is_completed') ?? 0;
                                            } else {
                                                $status = 0;
                                            }


                                            return [
                                                'exercise_id' => $exercise->exercise_id,
                                                'name' => $exercise->exercise->exercise_name ?? 'Unknown Exercise',
                                                'sets' => $exercise->sets,
                                                'reps' => $exercise->reps,
                                                'rest_seconds' => $exercise->rest_time,
                                                'order' => $exercise->order,
                                                'status' => $status,
                                                'video' => $exercise->exercise->video ?? null,
                                                'image' => $exercise->exercise->image ? url($exercise->exercise->image) : null,
                                                'level' => $exercise->exercise->level ?? null,
                                                'location' => $exercise->exercise->location ?? null,
                                                'body_part' => $exercise->exercise->bodyType->name ?? null,
                                            ];
                                        })->sortBy('order')->values()->toArray();
                                    })()

                                ];
                            })->sortBy('day_number')->values()->toArray()
                        ];
                    })->sortBy('week_number')->values()->toArray();

                // Calculate progress percentage
                $totalExercises = collect($weeks)->pluck('days')->flatten(1)->sum(function ($day) {
                    return $day['is_rest_day'] ? 0 : count($day['exercises']);
                });

                $completedExercises = collect($weeks)->pluck('days')->flatten(1)->sum(function ($day) {
                    return $day['is_rest_day'] ? 0 : count(array_filter($day['exercises'], fn($ex) => $ex['status'] == 1));
                });

                return [
                    'challenge_id' => $challenge->id,
                    'challenge_name' => $challenge->challenge_name,
                    'goal' => $challenge->goal,
                    'duration_weeks' => $challenge->duration_weeks,
                    // 'level' => $challenge->level,
                    'description' => $challenge->description,
                    'image' => $challenge->image ? url($challenge->image) : null,
                    'progress' => $totalExercises > 0 ? round(($completedExercises / $totalExercises) * 100) : 0,
                    'weeks' => $weeks
                ];
            });

            return $this->sendResponse($formattedChallenges, 'Challenges fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }


    public function getChallengeById(Request $request)
    {
        $request->validate([
            'challenge_id' => 'required|integer|exists:ft_fitness_challenges,id',
        ]);

        try {
            $challenge = FitnessChallenge::where('id', $request->challenge_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$challenge) {
                return $this->sendError('Challenge not found.', [], 404);
            }

            // Append full image path
            $challenge->image = url($challenge->image); // adjust path as needed

            return $this->sendResponse($challenge, 'Challenge details fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }

    public function saveWorkoutExerciseProgress(Request $request)
    {
        try {
            $user_id     = Auth::id();
            $week_id     = $request->input('week_id');
            $day_id      = $request->input('day_id');
            $workout_id  = $request->input('workout_id');
            $exerciseMap = $request->input('exercise_id'); // format: { "1": 1, "2": 0 }

            // Validation
            if (empty($week_id) || empty($day_id) || empty($workout_id) || empty($exerciseMap)) {
                return $this->sendError('Missing required fields.', [], 422);
            }

            // Get progress entry
            $progress = UserProgress::where('user_id', $user_id)
                ->where('week_id', $week_id)
                ->where('day_id', $day_id)
                ->where('workout_program_id', $workout_id)
                ->first();

            if (!$progress) {
                return $this->sendError('Progress entry not found.', [], 404);
            }

            $progress_id = $progress->id;
            $savedExercises = [];

            // Save new exercise progress if not already added
            foreach ($exerciseMap as $exercise_id => $completed) {
                // Check if record exists first
                $exerciseProgress = UserExerciseProgressWorkout::where([
                    'user_id'     => $user_id,
                    'exercise_id' => $exercise_id
                ])->first();

                if ($exerciseProgress) {
                    // Update existing record
                    $exerciseProgress->update([
                        'is_completed' => (int) $completed
                    ]);
                } else {
                    // Create new record
                    $exerciseProgress = UserExerciseProgressWorkout::create([
                        'progress_id'  => $progress_id,
                        'user_id'      => $user_id,
                        'exercise_id'  => $exercise_id,
                        'is_completed' => (int) $completed
                    ]);
                }

                $savedExercises[] = [
                    'exercise_id' => $exercise_id,
                    'status' => $exerciseProgress->is_completed
                ];
            }

            // Always update progress status
            $allValues = array_values($exerciseMap);
            $allTrue   = count(array_unique($allValues)) === 1 && $allValues[0] == 1;
            $allFalse  = count(array_unique($allValues)) === 1 && $allValues[0] == 0;

            if ($allTrue) {
                $progressStatus = 2;
            } elseif ($allFalse) {
                $progressStatus = 0;
            } else {
                $progressStatus = 1;
            }

            UserProgress::where('id', $progress_id)->update(['status' => $progressStatus]);

            $responseData = [
                'progress_id' => $progress_id,
                'exercises' => $savedExercises
            ];

            return $this->sendResponse($responseData, 'Exercise progress saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }


    public function saveChallengeExerciseProgress(Request $request)
    {
        try {
            $user_id = Auth::id();
            $week_id = $request->input('week_id');
            $day_id = $request->input('day_id');
            $challenge_id = $request->input('challenge_id');
            $exerciseMap = $request->input('exercise_id');

            // Validation
            if (empty($week_id) || empty($day_id) || empty($challenge_id) || empty($exerciseMap)) {
                return $this->sendError('Missing required fields.', [], 422);
            }
            $weekDays = FitnessChallengeWeekDay::where('fitness_challenge_id', $challenge_id)
                ->get(['id', 'week', 'day_number']);
            // Get or create UserChallengeProgress only for the current challenge/week/day

            foreach ($weekDays as $day) {
                $UserChallengeProgress = UserChallengeProgress::firstOrCreate([
                    'user_id' => $user_id,
                    'challenge_id' => $challenge_id,
                    'week_id' => $day->week,
                    'day_id' => $day->day_number,
                ], [
                    'start_date' => now(),
                    'status' => 0,
                ]);
            }

            $savedExercises = [];
            $completedCount = 0;
            $totalExercises = count($exerciseMap);


            // Get the UserChallengeProgress for the specific week/day
            $UserChallengeProgress = UserChallengeProgress::where('user_id', $user_id)
                ->where('challenge_id', $challenge_id)
                ->where('week_id', $week_id)
                ->where('day_id', $day_id)
                ->first();

            // Update only exercises for this specific day/week
            foreach ($exerciseMap as $exercise_id => $completed) {
                
                $exerciseProgress = UserExerciseProgressChallenge::updateOrCreate([
                    'progress_id' => $UserChallengeProgress->id,
                    'user_id' => $user_id,
                    'exercise_id' => $exercise_id,
                ], [
                    'is_completed' => (int) $completed,
                ]);

                $savedExercises[] = [
                    'exercise_id' => $exercise_id,
                    'status' => $exerciseProgress->is_completed
                ];

                if ($exerciseProgress->is_completed) {
                    $completedCount++;
                }
            }
            // Update the UserChallengeProgress status based on completion
            if ($completedCount == $totalExercises) {
                $UserChallengeProgress->status = 2; // Completed
            } elseif ($completedCount > 0) {
                $UserChallengeProgress->status = 1; // In Progress
            } else {
                $UserChallengeProgress->status = 0; // Not Started
            }
            $UserChallengeProgress->save();

            // Update day completion status
            $dayStatus = ($completedCount == $totalExercises) ? 2 : (($completedCount > 0) ? 1 : 0);
            // $progress->update(['status' => $dayStatus]);

            return $this->sendResponse([
                // 'progress_id' => $progress->id,
                'day_status' => $dayStatus,
                'exercises' => $savedExercises,
                'message' => 'Exercise progress updated for day ' . $day_id . ' week ' . $week_id
            ], 'Exercise progress saved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }
}
