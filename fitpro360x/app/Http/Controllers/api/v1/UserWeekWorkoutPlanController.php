<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workout_Programs;
use App\Models\Workout_Program_Exercises;
use App\Models\UserWorkoutPlan;
use App\Models\User;
use App\Models\Exercise;
use App\Models\Workout_Week_Days;
use Illuminate\Support\Facades\Log;
use App\Models\UserProgress;
use App\Models\UserExerciseProgressWorkout;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\QuestionAnswerUser;
use App\Models\Question;
use App\Models\QuestionsOption;


class UserWeekWorkoutPlanController extends BaseApiController
{ 
    
    
    /**
     * Get the current workout plan for the authenticated user.
     */
    public function getCurrentPlanNew(Request $request)
    {
        $user = $request->user();
        
        // Get the active workout plan with relationships
        $userPlan = $user->activeWorkoutPlan()->with(['workoutProgram'])->first();

        if (!$userPlan || !$userPlan->workoutProgram) {
            return $this->sendError('No active workout plan', [], 404);
        }

        $plan = $userPlan->workoutProgram;

        // pree($userPlan);
        $level_name = '';
        if($plan->level <= 3){
            $level_name = 'Beginner';
        } elseif($plan->level <= 6){
            $level_name = 'Intermediate';
        } elseif($plan->level <= 10){
            $level_name = 'Advance';
        } 

        $goal = QuestionAnswerUser::where('user_id', $user->id)
            ->where('question_id', 2)
            ->first();

        $optionData =  QuestionsOption::find($goal->option_id);

        $PlanData = [
            'id' => $plan->id,
            'title' => $plan->title,
            'goal' => $optionData->label_for_app,
            'duration_weeks' => $plan->duration_weeks,
            'level' => $level_name,
            'description' => $plan->description,
            'image' => $plan->image ? url($plan->image) : null,
        ];

        return $this->sendResponse($PlanData, 'Current workout plan retrieved successfully');
    }


    /**
     * Get all weeks of the current workout plan.
     */
    /**
 * Get all weeks of the current workout plan.
 */
    public function getCurrentPlanAllWeeks(Request $request, $WorkoutPlanId)
    {
        $user = $request->user();

        $userPlan = $user->activeWorkoutPlan()->with(['workoutProgram.weeks.exercises'])->first();

        if (!$userPlan || !$userPlan->workoutProgram) {
            return $this->sendError('No active workout plan', [], 404);
        }

        $plan = $userPlan->workoutProgram;
        $startDate = Carbon::parse($userPlan->start_date);
        $currentDate = Carbon::now();

        $weeks = $plan->weeks
            ->groupBy('week')
            ->map(function ($days, $weekIndex) use ($startDate, $currentDate, $user) {

                $weekIndex = (int) $weekIndex;
               // $is_week_active = $weekIndex == 1 ? 1 : (int) $days->first()->is_active;
                $is_week_active = $this->getWeekStatus($weekIndex, $days, $startDate);
                return [
                    'week_id' => (int) $weekIndex,
                    'is_active' => $is_week_active,
                    'days' => $days->sortBy('day_number')->map(function ($day) use ($weekIndex, $startDate, $currentDate, $user) {
                        // Calculate is_active based on calendar
                        //$dayOffset = (($weekIndex - 1) * 7) + ($day->day_number - 1);
                        //$dayDate = $startDate->copy()->addDays($dayOffset);

                        //$activeStatus = $dayDate->lt($currentDate) ? 2 : ($dayDate->isToday() ? 1 : 0);

                        /* $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                        $dayDate = $startDate->copy()->addDays($dayOffset);
                       

                        if ($dayDate->lt($currentDate)) {
                            $activeStatus = 2; // Past
                        } elseif ($dayDate->isToday()) {
                            $activeStatus = 1; // Today
                        } else {
                            $activeStatus = 0; // Future
                        } */


                        /* $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                        $dayDate = $startDate->copy()->addDays($dayOffset)->startOfDay();
                        $today = Carbon::today($dayDate->timezone)->startOfDay();

                        if ($dayDate->lt($today)) {
                            $activeStatus = 2; // Past
                        } elseif ($dayDate->eq($today)) {
                            $activeStatus = 1; // Today
                        } else {
                            $activeStatus = 0; // Future
                        } */

                        $activeStatus = $this->getDayActiveStatus($startDate, $day->week, $day->day_number);
                        // Status calculation
                        $status = $this->getDayStatus($day, $user, $activeStatus);

                        
                        /* // Compute exercise status
                        $status = (function () use ($day, $user) {
                            if ($day->is_rest_day) return 2;

                            $exercises = $day->exercises;
                            $total = $exercises->count();
                            if ($total === 0) return 0;

                            $progressId = UserProgress::where('user_id', $user->id)
                                ->where('workout_program_id', $day->workout_program_id)
                                ->where('week_id', $day->week)
                                ->where('day_id', $day->day_number)
                                ->value('id');

                            if (!$progressId) return 0;

                            $completed = $exercises->filter(function ($exercise) use ($progressId, $user) {
                                return UserExerciseProgressWorkout::where('exercise_id', $exercise->id)
                                    ->where('user_id', $user->id)
                                    ->where('progress_id', $progressId)
                                    ->latest()
                                    ->value('is_completed') == 1;
                            })->count();

                            return $completed === 0 ? 0 : ($completed === $total ? 2 : 1);
                        })(); */

                        return [
                            'day_number' => $day->day_number,
                            'is_rest_day' => (int) $day->is_rest_day,
                            'is_active' => $activeStatus,
                            'status' => $status,
                        ];
                    })->values(),
                ];
            })->values(); // reset numeric index (0, 1, 2...)

        return $this->sendResponse( $weeks, 'Current workout plan retrieved successfully');
    }

    /**
     * Get the current plan by specific week.
     */
    public function getCurrentPlanByWeek(Request $request, $id, $week_no)
    {
        $user = $request->user();

        // Get the active workout plan
        $userPlan = $user->activeWorkoutPlan()->with(['workoutProgram'])->first();
        if (!$userPlan || !$userPlan->workoutProgram) {
            return $this->sendError('No active workout plan', [], 404);
        }

        $plan = $userPlan->workoutProgram;
        $startDate = Carbon::parse($userPlan->start_date);
        $currentDate = Carbon::today();

        // Filter for the requested week
        $weekDays = $plan->weeks()
            ->where('week', $week_no)
            ->with(['exercises.exercise'])
            ->get();

        if ($weekDays->isEmpty()) {
            return $this->sendError('Week not found', [], 404);
        }
        $weekIndex = (int) $week_no;
        //$is_week_active = $weekIndex == 1 ? 1 : (int) $days->first()->is_active;

        $is_week_active = $this->getWeekStatus($weekIndex, $weekDays, $startDate);

        $weekData = [
            'week_id' => (int) $week_no,
            'is_active' => $is_week_active,
            'days' => $weekDays->sortBy('day_number')->map(function ($day) use ($user, $startDate, $currentDate) {
                // Calculate is_active based on date
                /* $dayOffset = (($day->week - 1) * 7) + ($day->day_number - 1);
                $dayDate = $startDate->copy()->addDays($dayOffset);

                if ($dayDate->lt($currentDate)) {
                    $activeStatus = 2; // Past
                } elseif ($dayDate->isToday()) {
                    $activeStatus = 1; // Today
                } else {
                    $activeStatus = 0; // Future
                } 
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


                //get week status
                $activeStatus = $this->getDayActiveStatus($startDate, $day->week, $day->day_number);
                // Status calculation
                $status = $this->getDayStatus($day, $user, $activeStatus);

                /*$status = (function () use ($day, $user, $activeStatus) {
                    if ($activeStatus === 0 && $day->is_rest_day) {
                        return 0; // Future rest day
                    }
                    if ($activeStatus === 1 && $day->is_rest_day) {
                        return 0; // Today rest day
                    }

                    if ($activeStatus === 2 && $day->is_rest_day) {
                        return 2; // Past rest day
                    }                     
                    if ($day->is_rest_day) return 0;
                    
                    
                    if ($activeStatus === 0) return 0; // Not active
                    
                   

                    $exercises = $day->exercises;
                    $totalExercises = $exercises->count();

                    if ($totalExercises === 0) return 0;

                    $progressId = UserProgress::where('user_id', $user->id)
                        ->where('workout_program_id', $day->workout_program_id)
                        ->where('week_id', $day->week)
                        ->where('day_id', $day->day_number)
                        ->value('id');

                    if (!$progressId) return 0;

                    $completedExercises = $exercises->filter(function ($exercise) use ($progressId, $user) {
                        return UserExerciseProgressWorkout::where('exercise_id', $exercise->id)
                            ->where('user_id', $user->id)
                            ->where('progress_id', $progressId)
                            ->latest()
                            ->value('is_completed') == 1;
                    })->count();

                    if($completedExercises === $totalExercises){
                        return 2; // All exercises completed
                    }

                    if ($activeStatus === 2 && $completedExercises === 0) {
                        return 1; // Past day with no completed exercises
                    }
                    if ($activeStatus === 1 && $completedExercises === 0) {
                        return 0; // Past day with no completed exercises
                    }
                    
                    if($activeStatus === 2 && $completedExercises != $totalExercises) {   
                        return 1; // Past day with no completed exercise
                    }
                    if($activeStatus === 1 && $completedExercises != $totalExercises) {
                        return 1; // Past day with completed exercises
                    }

                    return 1;
                })();*/

                return [
                    'day_id' => $day->day_number,
                    'is_rest_day' => (int) $day->is_rest_day,
                    'is_active' => $activeStatus,
                    'status' => $status,
                    'exercise_count' => $day->is_rest_day ? 0 : $day->exercises->count(),
                    'exercises' => [], // Only metadata required
                ];
            })
        ];

        return $this->sendResponse($weekData, 'Week workout plan retrieved successfully');
    }
    


    /**
     * Get the current plan by specific week and day.
     */
   public function getCurrentPlanByWeekDay(Request $request, $id, $week_no, $day)
    {
        $user = $request->user();
        \Log::info('Fetching current workout day for user', [
            'user_id' => $user->id,
            'week_no' => $week_no,
            'day' => $day,
        ]);

        $userPlan = $user->activeWorkoutPlan()->with('workoutProgram')->first();
        if (!$userPlan || !$userPlan->workoutProgram) {
            \Log::warning('No active workout plan found for user', ['user_id' => $user->id]);
            return $this->sendError('No active workout plan', [], 404);
        }

        $plan = $userPlan->workoutProgram;
        $plan->load(['weeks.days.exercises.exercise', 'weeks.days.exercises.muscles','userProgress']);

        $startDate = Carbon::parse($userPlan->start_date);
        $currentDate = Carbon::today();

        // Fetch day model
        $dayModel = $plan->weeks
            ->where('week', (int) $week_no)
            //->pluck('days')
            //->flatten()
            ->firstWhere('day_number', (int) $day);

        if (!$dayModel) {
            \Log::warning('Workout day not found', [
                'week_no' => $week_no,
                'day' => $day,
                'workout_program_id' => $plan->id,
            ]);
            return $this->sendError('Workout day not found', [], 404);
        }

        // Calculate day offset and active status
        $week_no = (int) $week_no;
        $day = (int) $day;
        $dayOffset = (($week_no - 1) * 7) + ($day - 1);
        $dayDate = $startDate->copy()->addDays($dayOffset);
        $activeStatus = $dayDate->lt($currentDate) ? 2 : ($dayDate->isToday() ? 1 : 0);

        $dayModel->update(['is_active' => $activeStatus]);

        \Log::info('Day model found and active status calculated', [
            'day_id' => $dayModel->id,
            'is_rest_day' => $dayModel->is_rest_day,
            'is_active' => $activeStatus,
            'date_of_day' => $dayDate->toDateString(),
        ]);

        // Get user progress ID
        $progressId = UserProgress::where('user_id', $user->id)
            ->where('workout_program_id', $dayModel->workout_program_id)
            ->where('week_id', $week_no)
            ->where('day_id', $day)
            ->value('id');

        \Log::info('Progress ID fetched', [
            'progress_id' => $progressId,
        ]);

        // Exercises
        $exercises = !$dayModel->is_rest_day ? $dayModel->exercises->map(function ($exercise) use ($user, $progressId) {
            $query = UserExerciseProgressWorkout::where('exercise_id', $exercise->id)
                ->where('user_id', $user->id);

            if ($progressId) {
                $query->where('progress_id', $progressId);
            }

            $status = $query->latest()->value('is_completed') ?? 0;    

            \Log::info('Exercise status fetched', [
                'exercise_id' => $exercise->id,
                'exercise_name'=> $exercise->exercise->exercise_name ?? 'Unknown Exercise',
                'status' => $status,
            ]);
            return [
                'id' => $exercise->id,
                'name' => $exercise?->exercise?->exercise_name ?? 'Unknown Exercise',
                'sets' => $exercise->sets,
                'reps' => $exercise->reps,
                'rest_seconds' => $exercise->rest_seconds,
                'order' => $exercise->order,
                'video' => $exercise?->exercise?->video ? url($exercise->exercise->video) :  null,
                'image' => $exercise?->exercise?->image ? url($exercise->exercise->image) : null,
                'status' => (int) $status,
                'instructions' => $exercise?->exercise?->description ?? '',
                'muscles' => $exercise?->exercise?->muscles ? $exercise->exercise->muscles->map(function ($muscle) {
                    return [
                        'id' => $muscle->id,
                        'name' => $muscle->name,
                    ];
                })->toArray() : [],
            ];
        })->sortBy('order')->values()->toArray() : [];

        // Status calculation
        $totalExercises = $dayModel?->exercises?->count();
        $completedCount = $dayModel->is_rest_day ? 0 : collect($exercises)->where('status', 1)->count();

        $dayStatus = $dayModel->is_rest_day ? 2 : (
            $completedCount === 0 ? 0 : (
                $completedCount === $totalExercises ? 2 : 1
            )
        );

        \Log::info('Workout day data prepared', [
            'day_status' => $dayStatus,
            'exercise_count' => $totalExercises,
            'completed_count' => $completedCount,
            'exercises' => count($exercises)
        ]);

        // Final response
        return $this->sendResponse([
            'week_id' => $week_no,
            'day_id' => $day,
            'is_rest_day' => (int) $dayModel->is_rest_day,
            'is_active' => $activeStatus,
            'status' => $dayStatus,
            'exercises' => $exercises
        ], 'Workout day retrieved successfully');
    }



    
    
    protected function determineActiveWeek($plan)
    {
        // Get all weeks ordered by week number
        $weeks = $plan->weeks->sortBy('week');

        // Check if we have any user progress
        if ($plan->userProgress) {
            // Find the highest completed week
            $completedWeeks = $plan->userProgress()
                ->where('status', 2) // 2 = completed
                ->get()
                ->unique('week');

            if ($completedWeeks->isNotEmpty()) {
                $lastCompletedWeek = $completedWeeks->first()->week;

                // Find the next week that isn't completed
                foreach ($weeks as $week) {
                    if ($week->week > $lastCompletedWeek) {
                        return $week->week;
                    }
                }

                // If all weeks after last completed are done, return the last week
                return $weeks->last()->week;
            }
        }

        // Default to first week if no progress
        return $weeks->first()->week;
    }



    function getExerciseDetails(Request $request, $id, $exercise_id)
    {
        $exercise = Exercise::with(['muscles'])->find($exercise_id);

        if (!$exercise) {
            return $this->sendError('Exercise not found', [], 404);
        }

        return $this->sendResponse([
            'id' => $exercise->id,
            'name' => $exercise->exercise_name,
            'description' => $exercise->description,
            'sets' => $exercise->sets,
            'reps' => $exercise->reps,
            'rest_seconds' => $exercise->rest_seconds,
            'order' => $exercise->order,
            'video' => $exercise?->video,
            'image' => $exercise?->image,
            'instructions' => $exercise->description,
            'muscles' => $exercise->muscles->map(function ($muscle) {
                return [
                    'id' => $muscle->id,
                    'name' => $muscle->name
                ];
            })
        ], 'Exercise details retrieved successfully');
    }

    private function getDayStatus($day, $user, $activeStatus)
    {
        if ($activeStatus === 0 && $day->is_rest_day) {
            return 0; // Future rest day
        }
        if ($activeStatus === 1 && $day->is_rest_day) {
            return 0; // Today rest day
        }
    
        if ($activeStatus === 2 && $day->is_rest_day) {
            return 2; // Past rest day
        }
    
        if ($day->is_rest_day) return 0;
    
        if ($activeStatus === 0) return 0; // Not active
    
        $exercises = $day->exercises;
        $totalExercises = $exercises->count();
    
        if ($totalExercises === 0) return 0;
    
        $progressId = UserProgress::where('user_id', $user->id)
            ->where('workout_program_id', $day->workout_program_id)
            ->where('week_id', $day->week)
            ->where('day_id', $day->day_number)
            ->value('id');
    
        if (!$progressId) return 0;
    
        $completedExercises = $exercises->filter(function ($exercise) use ($progressId, $user) {
            return UserExerciseProgressWorkout::where('exercise_id', $exercise->id)
                ->where('user_id', $user->id)
                ->where('progress_id', $progressId)
                ->latest()
                ->value('is_completed') == 1;
        })->count();
        
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
    
        return 1;
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

    private function getWeekStatus($weekNumber, $days, $startDate)
    {
        // First week is always active
        if ((int)$weekNumber === 1) {
            \Log::info('Week 1 is always active', ['weekNumber' => $weekNumber]);
            return 1;
        }

        // If no days provided, return inactive
        if (empty($days) || count($days) === 0) {
            \Log::info('No days found for week', ['weekNumber' => $weekNumber]);
            return 0;
        }

        // Check if any day in the week is active
        foreach ($days as $day) {
            if ((int)$day->is_active === 1 || (int)$day->is_active === 2) {
                \Log::info('Week has an active day', [
                    'weekNumber' => $weekNumber,
                    'day_id' => $day->id ?? null,
                    'is_active' => $day->is_active,
                ]);
                return 1;
            }
        }

        // Check if startDate is set
        if (empty($startDate)) {
            \Log::warning('Start date is missing', ['weekNumber' => $weekNumber]);
            return 0;
        }

        // Calculate week start date
        $weekStartDate = $startDate->copy()->addDays(($weekNumber - 1) * 7)->startOfDay();
        $today = Carbon::today($weekStartDate->timezone)->startOfDay();

        \Log::info('Comparing week start with today', [
            'weekNumber' => $weekNumber,
            'weekStartDate' => $weekStartDate->toDateString(),
            'today' => $today->toDateString(),
        ]);

        if ($weekStartDate->lte($today)) {
            \Log::info('Week is in the past or starts today - active', ['weekNumber' => $weekNumber]);
            return 1;
        }

        \Log::info('Week is in the future - inactive', ['weekNumber' => $weekNumber]);
        return 0;
    }


   private function old_getWeekStatus($weekNumber, $days, $startDate)
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
        
            \Log::info('Fetching current workout day for user', [
            'user_id' => $user->id,
            'week_no' => $week_no,
            'day' => $day,
        ]);
        // If the week's start date is in the past or today, it's considered active
        if ($weekStartDate->lte($today)) {
            return 1;
        }

        // If week starts in the future or today, it's active
        if ($weekStartDate->gte($today)) {
            return 1;
        }


        // No active days found
        return 0;
    }


    //======old============================
    
    
    
    
    
    public function getCurrentPlan()
    {
        $plan = Auth::user()->activeWorkoutPlan;

        if (!$plan) {
            return $this->sendError('No active workout plan', [], 404);
        }

        return $this->sendResponse([
            'plan' => $plan,
            'current_workout' => $plan->getCurrentDayWorkout(),
            'progress' => $plan->progress
        ], 'Current workout plan retrieved successfully');
    }

    public function getWeekDayExercises(Request $request)
    {
        $request->validate([
            'week_number' => 'required|integer',
            'day_number' => 'required|integer'
        ]);

        $user = Auth::user();
        
        // Get the active workout plan
        $userPlan = $user->activeWorkoutPlan()->with(['workoutProgram'])->first();

        if (!$userPlan || !$userPlan->workout_program_id) { 
            return $this->sendError('No active workout plan', [], 404);
        }

        $plan = Workout_Programs::find($userPlan->workout_program_id);

        // Find the specific week day with exercises
        $weekDay = Workout_Week_Days::where('workout_program_id', $plan->id)
            ->where('week', $request->week_number)
            ->where('day_number', $request->day_number)
            ->with(['exercises' => function($query) {
                $query->with(['exercise' => function($q) {
                    $q->select('id', 'exercise_name', 'description', 'video', 'image');
                }])
                ->orderBy('order', 'asc');
            }, 'userProgress'])
            ->first();

        if (!$weekDay) {
            return $this->sendError('Workout day not found', [], 404);
        }

        // Get or create user progress
        $progress = $weekDay->userProgress ?? $this->getDefaultProgress($weekDay, $user);

        // Format the response
        $response = [
            'plan_id' => $plan->id,
            'plan_title' => $plan->title,
            'week_number' => $request->week_number,
            'day_number' => $request->day_number,
            'is_rest_day' => (int)$weekDay->is_rest_day,
            'progress' => [
                'is_active' => (int)$progress->is_active,
                'status' => (int)$progress->status,
                'completed_at' => $progress->completed_at
            ],
            'exercises' => $weekDay->is_rest_day ? [] : $weekDay->exercises->map(function($exercise) {
                return [
                    'id' => $exercise->id,
                    'name' => $exercise->exercise->exercise_name ?? 'Unknown Exercise',
                    'description' => $exercise->exercise->description ?? '',
                    'sets' => $exercise->sets,
                    'reps' => $exercise->reps,
                    'rest_seconds' => $exercise->rest_seconds,
                    'order' => $exercise->order,
                    'video' => $exercise?->exercise?->video ? url($exercise->exercise->video) :  null,
                    'image' => $exercise?->exercise?->image ? url($exercise->exercise->image):  null,
                    'instructions' => $exercise->exercise->instructions ?? []
                ];
            })
        ];

        return $this->sendResponse($response, 'Workout day exercises retrieved successfully');
    }
    protected function getDefaultProgress($weekDay, $user)
    {
        return new UserProgress([
            'is_active' => $weekDay->week == 1 && $weekDay->day_number == 1 ? 1 : 0,
            'status' => 0,
            'user_id' => $user->id,
            'week_id' => $weekDay->week,
            'day_id' => $weekDay->day_number,
            'workout_program_id' => $weekDay->workout_program_id,
            'completed_at' => null
        ]);
    } 
    /**
     * Mark current day as complete
     */
    public function completeDay(UserWorkoutPlan $plan)
    {
        if ($plan->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized', [], 403);
        }

        $plan->completeDay();

        return $this->sendResponse([
            'message' => 'Workout day completed',
            'next_workout' => $plan->getCurrentDayWorkout(),
            'progress' => $plan->progress
        ], 'Workout day completed successfully');
    }

    public function assignPlan(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:ft_users,id',
            'start_date' => 'nullable|date|after_or_equal:today|sometimes'
        ]);
    
        $user = User::with(['questionAnswers' => function($query) {
            $query->whereIn('question_id', [2, 5, 15]); 
        }])->findOrFail($validated['user_id']);
    
        // Safely extract answers with defaults
        $answers = $user->questionAnswers->keyBy('question_id');
        
        $goal = $answers[2]->answer ?? null;
        $location = $answers[15]->answer ?? 1; // default home
        $level = $answers[5]->answer ?? 'beginner'; // default beginner
    
        if (!$goal) {
            return $this->sendError('User has not completed required questionnaire', [], 400);
        }
    
        $program = Workout_Programs::query()
        ->where('goal', $goal)
        ->where('location', $location)
        ->when($level, fn($q) => $q->where('level', $level))
        ->first();

    // Fallback 1: Same goal + location, any level
    if (!$program) {
        $program = Workout_Programs::where('goal', $goal)
            ->where('location', $location)
            ->first();
    }

    // Fallback 2: Same goal, any location/level
    if (!$program) {
        $program = Workout_Programs::where('goal', $goal)
            ->orderBy('location', 'asc') // Prefer home(1) over gym(2)
            ->first();
    }

    // Final fallback: First available program
    if (!$program) {
        $program = Workout_Programs::orderBy('created_at')->first();
        
        if (!$program) {
            return response()->json([
                'status' => false,
                'message' => 'No workout programs available in system'
            ], 404);
        }
    }
    
        // Rest of your plan creation logic...
        
        $plan = UserWorkoutPlan::create([
            'user_id' => $user->id,
            'workout_program_id' => $program->id,
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
            'created_by' => Auth::id()
        ]);
    
        return $this->sendResponse([
            'plan' => $plan,
            'match_accuracy' => $this->calculateMatchAccuracy($program, $goal, $location, $level)
        ], 'Personalized plan assigned');
    }
    
    protected function calculateMatchAccuracy($program, $goal, $location, $level)
    {
        $score = 0;
        if ($program->goal === $goal) $score += 40;
        if ($program->location === $location) $score += 30;
        if ($program->level === $level) $score += 30;
        
        return $score;
    }
    /**
     * Admin: Get all plans for a user
     */
    public function getUserPlans($userId)
    {
        $plans = UserWorkoutPlan::with(['program', 'createdBy'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($plans, 'User plans retrieved successfully');
    }
}
