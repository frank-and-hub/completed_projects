<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Workout_Programs;
use App\Models\UserWorkoutPlan;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Workout_Week_Days;
use App\Models\UserProgress;
use App\Http\Controllers\Controller;
use App\Http\Controllers\api\v1\BaseApiController;

class UserWorkoutPlanController extends BaseApiController
{
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
                'video' => $exercise->exercise->video ?? null,
                'image' => $exercise->exercise->image ?? null,
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
