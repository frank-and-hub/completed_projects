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
use App\Models\ChallengePackages;
use App\Models\UserChallengeSubscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChallengeWeekController extends BaseApiController
{



    // 1. Get current challenge plan list (only challenge metadata)
    public function challengesPlan()
    {
        $user_id = Auth::id();

        $challenges = FitnessChallenge::whereNull('deleted_at')
            ->with(['weekDays' => function ($query) {
                $query->select('id', 'fitness_challenge_id', 'week');
            }])
            ->get(['id', 'challenge_name', 'goal', 'duration_weeks', 'image', 'description', 'plan_id']);

        if ($challenges->isEmpty()) {
            return $this->sendError('No challenges found.', [], 404);
        }

        // Format the image URLs and count weeks
        $formattedChallenges = $challenges->map(function ($challenge) {
            $totalWeeks = $challenge->weekDays
                ->pluck('week')
                ->unique()
                ->count();

            return [
                'id' => $challenge->id,
                'challenge_name' => $challenge->challenge_name,
                'goal' => $challenge->goal,
                'duration_weeks' => $challenge->duration_weeks,
                'total_weeks' => $totalWeeks,
                'description' => $challenge->description,
                'image' => $challenge->image ? url($challenge->image) : null,
                'plan_id' => $challenge->plan_id,
                'subscription_plan' => $challenge->plan,

            ];
        });

        return $this->sendResponse($formattedChallenges, 'Challenges retrieved successfully.');
    }


    public function getAllWeeksDataByChallengeId($id)
    {
        try {
            $user_id = Auth::id();

            $challenges = FitnessChallenge::where('id', $id)
                ->whereNull('deleted_at')
                ->get(['id', 'challenge_name', 'goal', 'duration_weeks', 'image', 'description']);

            if ($challenges->isEmpty()) {
                return $this->sendError('No challenges found.', [], 404);
            }

            $userProgress = UserChallengeProgress::with('exerciseProgress')
                ->where('user_id', $user_id)
                ->whereIn('challenge_id', $challenges->pluck('id'))
                ->get()
                ->groupBy(['challenge_id', 'week_id', 'day_id']);

            $challengeStartDate = UserChallengeProgress::where('user_id', $user_id)
                ->where('challenge_id', $id)
                ->value('start_date');
            // print_r($challengeStartDate); exit;
            $formattedChallenges = $challenges->map(function ($challenge) use ($userProgress, $challengeStartDate, $user_id) {
                $challenge->load(['weekDays']);

                $challengeProgress = $userProgress->get($challenge->id) ?? collect();

                $weeks = $challenge->weekDays
                    ->groupBy('week')
                    ->map(function ($days, $weekNumber) use ($challengeProgress, $challengeStartDate) {

                        $weekProgress = $challengeProgress->get($weekNumber) ?? collect();
                        //$weekIsActive = $weekNumber == 1 ? 1 : ($days->contains(fn($day) => $day->is_active == 1) ? 1 : 0);
                        $weekIsActive = 0;

                        $startDate = Carbon::parse($challengeStartDate);

                        $weekIsActive = $this->getWeekStatus($weekNumber, $days,  $startDate);
                        return [
                            'week_id' => $weekNumber,
                            'is_active' => $weekIsActive,
                            'days' => $days->sortBy('day_number')->map(function ($day) use ($weekNumber, $weekProgress) {
                                $dayProgress = $weekProgress->get($day->id);
                                $progressRecord = UserChallengeProgress::where('user_id', Auth::id())
                                    ->where('challenge_id', $day->fitness_challenge_id)
                                    ->first();

                                $startDate = $progressRecord?->start_date;

                                $is_active = 0;
                                /*if ($startDate) {
                                    $start = Carbon::parse($startDate);
                                    $today = Carbon::today();
                                    $dayOffset = (($weekNumber - 1) * 7) + ($day->day_number - 1);
                                    $dayDate = $start->copy()->addDays($dayOffset);

                                    $is_active = $dayDate->lt($today) ? 2 : ($dayDate->isToday() ? 1 : 0);
                                }

                                $status = 0;
                                 if ($day->is_rest_day) {
                                    $status = 2;
                                } else {
                                    $progressId = UserChallengeProgress::where('user_id', Auth::id())
                                        ->where('challenge_id', $day->fitness_challenge_id)
                                        ->where('week_id', $day->week)
                                        ->where('day_id', $day->day_number)
                                        ->value('id');

                                    if ($progressId) {
                                        $completedExercises = UserExerciseProgressChallenge::where('user_id', Auth::id())
                                            ->where('progress_id', $progressId)
                                            ->where('is_completed', 1)
                                            ->count();

                                        $totalExercises = UserExerciseProgressChallenge::where('user_id', Auth::id())
                                            ->where('progress_id', $progressId)
                                            ->count();

                                        if ($completedExercises === 0) {
                                            $status = 0;
                                        } elseif ($completedExercises === $totalExercises) {
                                            $status = 2;
                                        } else {
                                            $status = 1;
                                        }
                                    }
                                } */
                                $startDate = Carbon::parse($startDate);
                                /* $today = Carbon::today();
                                $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                                $dayDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
                                $today = Carbon::today($dayDate->timezone)->startOfDay();

                                if ($dayDate->lt($today)) {
                                    $activeStatus = 2; // Past
                                } elseif ($dayDate->eq($today)) {
                                    $activeStatus = 1; // Today
                                } else {
                                    $activeStatus = 0; // Future
                                }*/
                                $activeStatus = $this->getDayActiveStatus($startDate, $weekNumber, $day->day_number);
                                $is_active = $activeStatus;
                                $status = 0;
                                $status = $this->getDayStatus($day, Auth::user(), $weekNumber, $is_active);

                                return [
                                    'day_number' => $day->day_number,
                                    'is_active' => $is_active,
                                    'is_rest_day' => $day->is_rest_day ? 1 : 0,
                                    'status' => $status
                                ];
                            })->sortBy('day_number')->values()->toArray()
                        ];
                    })->sortBy('week_id')->values()->toArray();



                // ✅ Calculate progress based on completed non-rest days
                $activeDays = collect($weeks)->pluck('days')->flatten(1)->filter(function ($day) {
                    return $day['is_rest_day'] == 0;
                });

                $totalDays = $activeDays->count();
                $completedDays = $activeDays->where('status', 2)->count();

                $progressPercentage = $totalDays > 0 ? round(($completedDays / $totalDays) * 100) : 0;

                //get UserChallengeSubscription for logged in user where deted_at is null and status is active
                $userChallengeSubscription = UserChallengeSubscription::where('user_id', $user_id)
                    ->where('challenge_id', $challenge->id)
                    ->whereNull('deleted_at')
                    ->where('status', 'active')
                    ->first();


                return [
                    'challenge_id' => $challenge->id,
                    'challenge_name' => $challenge->challenge_name,
                    'goal' => $challenge->goal,
                    'duration_weeks' => $challenge->duration_weeks,
                    'description' => $challenge->description,
                    'image' => $challenge->image ? url($challenge->image) : null,
                    'progress' => $progressPercentage,
                    'weeks' => $weeks,
                    'is_subscribed' => ($userChallengeSubscription && $userChallengeSubscription->status === 'active') ? 1 : 0,

                ];
            });

            return $this->sendResponse($formattedChallenges, 'Challenges fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }

    public function challengesPlanByWeek($id, $week_no)
    {
        try {
            $user_id = Auth::id();

            $challenge = FitnessChallenge::with(['weekDays.exercises.exercise.bodyType'])
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (!$challenge) {
                return $this->sendError('Challenge not found.', [], 404);
            }

            $days = $challenge->weekDays
                ->where('week', $week_no)
                ->sortBy('day_number')
                ->values();

            if ($days->isEmpty()) {
                return $this->sendError('Week data not found.', [], 404);
            }

            // Get progress records for the week
            $progressMap = UserChallengeProgress::with('exerciseProgress')
                ->where('user_id', $user_id)
                ->where('challenge_id', $id)
                ->where('week_id', $week_no)
                ->get()
                ->groupBy('day_id');

            $startDate = UserChallengeProgress::where('user_id', $user_id)
                ->where('challenge_id', $id)
                ->value('start_date');

            $today = Carbon::today();
            //check if the week is active based on the start date and today's date in next weeks if one day is active then week is active
            $weekNumber = (int) $week_no;

            $weekStartDate = Carbon::parse($startDate);
            $weekIsActive = $this->getWeekStatus($weekNumber, $days, $weekStartDate);


            /* $weekIsActive = $days->contains(function ($day) use ($startDate) {
                if (!$startDate) return false;
                $start = Carbon::parse($startDate);
                $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                $dayDate = $start->copy()->addDays($dayOffset);
                return $dayDate->isToday() || $dayDate->lt(Carbon::now());
            }) ? 1 : 0; */



            $formattedDays = $days->sortBy('day_number')->map(function ($day) use ($week_no, $user_id, $startDate, $progressMap) {
                $dayProgress = $progressMap->get($day->day_number)?->first();
                $progressId = $dayProgress?->id;

                /* $is_active = 0;
                if ($startDate) {
                    $start = Carbon::parse($startDate);
                    $dayOffset = (($week_no - 1) * 7) + ($day->day_number - 1);
                    $dayDate = $start->copy()->addDays($dayOffset);

                    if ($dayDate->lt(Carbon::today())) {
                        $is_active = 2; // Past
                    } elseif ($dayDate->isToday()) {
                        $is_active = 1; // Today
                    }
                }

                $exercises = $day->exercises ?? collect();
                $totalExercises = $exercises->count();

                $completedExercises = $exercises->filter(function ($exercise) use ($user_id, $progressId) {
                    return UserExerciseProgressChallenge::where('exercise_id', $exercise->exercise_id)
                        ->where('user_id', $user_id)
                        ->when($progressId, fn($q) => $q->where('progress_id', $progressId))
                        ->latest()
                        ->value('is_completed') == 1;
                })->count();

                $status = 0;
                if ($day->is_rest_day) {
                    $status = 2;
                } elseif ($totalExercises > 0) {
                    $status = $completedExercises === $totalExercises ? 2 : ($completedExercises > 0 ? 1 : 0);
                } */
                $is_active = 0;
                //pree($startDate);
                $startDate = Carbon::parse($startDate);
                /*$today = Carbon::today();
                $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                $dayDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
                $today = Carbon::today($dayDate->timezone)->startOfDay();

                if ($dayDate->lt($today)) {
                    $activeStatus = 2; // Past
                } elseif ($dayDate->eq($today)) {
                    $activeStatus = 1; // Today
                } else {
                    $activeStatus = 0; // Future
                }*/
                // get getDayActiveStatus    
                $activeStatus = $this->getDayActiveStatus($startDate, $week_no, $day->day_number);

                $is_active = $activeStatus;
                $status = 0;
                $status = $this->getDayStatus($day, Auth::user(), $week_no, $is_active);


                $exercises = $day->exercises ?? collect();
                $totalExercises = $exercises->count();

                return [
                    'day_id' => $day->day_number,
                    'is_rest_day' => $day->is_rest_day ? 1 : 0,
                    'is_active' => $is_active,
                    'status' => $status,
                    'exercise_count' => $totalExercises
                ];
            });

            return $this->sendResponse([
                'week_id' => (int) $week_no,
                'is_active' => $weekIsActive,
                'days' => $formattedDays
            ], 'Challenge week data fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }

    public function challengesPlanByWeekDay(Request $request, $challenge_id, $week_no, $day_no)
    {
        try {
            $userId = Auth::id();

            // Load the specific weekday with relationships
            $day = FitnessChallengeWeekDay::with(['exercises.exercise'])
                ->where('fitness_challenge_id', $challenge_id)
                ->where('week', $week_no)
                ->where('day_number', $day_no)
                ->first();

            if (!$day) {
                return $this->sendError('Day not found.', [], 404);
            }

            $progressRecord = UserChallengeProgress::where('user_id', $userId)
                ->where('challenge_id', $challenge_id)
                ->first();

            $startDate = $progressRecord?->start_date;

            $isActive = 0;
            /*if ($startDate) {
                $start = \Carbon\Carbon::parse($startDate);
                $today = \Carbon\Carbon::today();
                $dayOffset = (($week_no - 1) * 7) + ($day_no - 1);
                $dayDate = $start->copy()->addDays($dayOffset);

                if ($dayDate->lt($today)) {
                    $isActive = 2; // Past
                } elseif ($dayDate->isToday()) {
                    $isActive = 1; // Today
                } else {
                    $isActive = 0; // Future
                }
            }*/

            // Parse the start date
            $startDate = \Carbon\Carbon::parse($startDate);
            //getDayActiveStatus()
            $activeStatus = $this->getDayActiveStatus(Carbon::parse($startDate), $week_no, $day_no);
            $isActive = $activeStatus;

            $progressId = UserChallengeProgress::where('user_id', $userId)
                ->where('challenge_id', $challenge_id)
                ->where('week_id', $week_no)
                ->where('day_id', $day_no)
                ->value('id');

            
            $exercises = $day->exercises->map(function ($exercise) use ($userId, $progressId) {
                $query = UserExerciseProgressChallenge::where('exercise_id', $exercise->id)
                    ->where('user_id', $userId);

                if ($progressId) {
                    $query->where('progress_id', $progressId);
                }

                // dd($query->toSql(), $query->getBindings());

                $isCompleted = optional(
                    $query->orderByDesc('id')->first()
                )->is_completed ?? 0;
                // $isCompleted = $query->orderByDesc('id')->value('is_completed') ?? 0;

                // pre($exercise->id . '=', 'ss');


                return [
                    'id' => $exercise->id,
                    'name' => $exercise->exercise->exercise_name ?? 'Unknown',
                    'sets' => $exercise->sets,
                    'reps' => $exercise->reps,
                    'rest_seconds' => $exercise->rest_time,
                    'order' => $exercise->order,
                    'video' => $exercise->exercise->video ? url($exercise->exercise->video) : null,
                    'image' => $exercise->exercise->image ? url($exercise->exercise->image) : null,
                    'status' => $isCompleted,
                    'instructions' => $exercise->exercise->description ?? null,
                    'muscles' => $exercise->exercise->muscles ? $exercise->exercise->muscles->map(function ($muscle) {
                        return [
                            'id' => $muscle->id,
                            'name' => $muscle->name,
                        ];
                    })->toArray() : [],
                ];
            });

            $total = $exercises->count();
            $completed = $exercises->where('status', 1)->count();

            $status = 0;
            if ($total > 0 && $completed == $total) {
                $status = 2;
            } elseif ($completed > 0) {
                $status = 1;
            }
            $status = $this->getDayStatus($day, Auth::user(), $week_no, $isActive);

            return response()->json([
                'success' => true,
                'data' => [
                    'week_id' => (int)$week_no,
                    'day_id' => (int)$day_no,
                    'is_rest_day' => $day->is_rest_day ? 1 : 0,
                    'is_active' => $isActive,
                    'status' => $status,
                    'exercises' => $day->is_rest_day ? [] : $exercises->values()->toArray()
                ],
                'message' => 'Workout day retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }



    private function getDayStatus($day, $user, $weekNumber, $activeStatus)
    {
        /* $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
        $dayDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
        $today = Carbon::today($dayDate->timezone)->startOfDay();

        // Determine the active status
        if ($dayDate->lt($today)) {
            $activeStatus = 2; // Past
        } elseif ($dayDate->eq($today)) {
            $activeStatus = 1; // Today
        } else {
            $activeStatus = 0; // Future
        } */

        // Rest day handling
        if ($activeStatus === 0 && $day->is_rest_day) return 0; // Future rest day
        if ($activeStatus === 1 && $day->is_rest_day) return 0; // Today rest day
        if ($activeStatus === 2 && $day->is_rest_day) return 2; // Past rest day
        if ($day->is_rest_day) return 0;


        // Not active (future) day
        if ($activeStatus === 0) return 0;

        // Fetch progress ID
        $progressId = UserChallengeProgress::where('user_id', $user->id)
            ->where('challenge_id', $day->fitness_challenge_id)
            ->where('week_id', $day->week)
            ->where('day_id', $day->day_number)
            ->value('id');

        // If no progress record found, treat as not started
        if (!$progressId) return 0;

        // Get total and completed exercises
        $completedExercises = UserExerciseProgressChallenge::where('user_id', $user->id)
            ->where('progress_id', $progressId)
            ->where('is_completed', 1)
            ->count();
        $exercises = $day->exercises;
        $totalExercises = $exercises->count();
        /*( $totalExercises = UserExerciseProgressChallenge::where('user_id', $user->id)
            ->where('progress_id', $progressId)
            ->count(); */

        //if ($totalExercises === 0) return 0;

        if ($completedExercises === $totalExercises) {
            //return 2; // All completed
        }

        if ($completedExercises === 0) {
            //return ($activeStatus === 1) ? 0 : 1; // Today = 0, Past = 1
        }
        //echo $completedExercises .'-----'. $totalExercises;    exit;


        if ($completedExercises === $totalExercises) {
            return 2; // All exercises completed
        }

        if ($activeStatus === 2 && $completedExercises === 0) {
            return 1; // Past day with no completed exercises
        }

        if ($activeStatus === 1 && $completedExercises === 0) {
            return 0; // Today with no completed exercises
        }

        if ($activeStatus === 2 && $completedExercises != $totalExercises) {
            return 1; // Past day with some completed exercises
        }

        if ($activeStatus === 1 && $completedExercises != $totalExercises) {
            return 1; // Today with some completed exercises
        }

        return 1; // Some completed
    }

    private function getDayActiveStatus($startDate, $week, $dayNumber)
    {
        if (!$startDate || !$week || !$dayNumber) {
            return null; // invalid input
        }

        $dayOffset = (($week - 1) * 7) + ($dayNumber - 1);
        $dayDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
        $today = Carbon::today($dayDate->timezone)->startOfDay();

        if ($dayDate->lt($today)) {
            return 2; // Past
        } elseif ($dayDate->eq($today)) {
            return 1; // Today
        } else {
            return 0; // Future
        }
    }

    private function getWeekStatus($weekNumber, $days, $startDate = null)
    {

        // First week is always active
        if ((int)$weekNumber === 1) {
            return 1;
        }

        // If no days provided, return inactive
        if (empty($days) || count($days) === 0) {
            return 0;
        }

        // Check if any day in the week is active
        foreach ($days as $day) {
            if ((int)$day->is_active === 1 || (int)$day->is_active === 2) {
                return 1;
            }
        }

        // If no active days found, check the start date of the week
        if (empty($startDate)) {
            return 0;
        }

        // Calculate the start date of the week
        $weekStartDate = $startDate->copy()->addDays(($weekNumber - 1) * 7)->startOfDay();
        $today = Carbon::today($weekStartDate->timezone)->startOfDay();

        // If week starts in the future or today, it's active
        if ($weekStartDate->lte($today)) {
            //\Log::info('Week is in the past or starts today - active', ['weekNumber' => $weekNumber]);
            return 1;
        }

        // No active days found
        return 0;
    }
    //=================================================================================old===============================


    public function old_getChallenges()
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
                                                'video' => $exercise->exercise->video ? url($exercise->exercise->video) : null,
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
                UserChallengeProgress::firstOrCreate([
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

            // Update only exercises for this specific day/week
            foreach ($exerciseMap as $exercise_id => $completed) {
                $exerciseProgress = UserExerciseProgressChallenge::updateOrCreate([
                    // 'progress_id' => $weekDays->id,
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
