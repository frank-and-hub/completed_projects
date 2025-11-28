<?php

namespace App\Http\Controllers\admin;

use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\BodyType;
use App\Models\FitnessChallengeExercise;
use App\Models\FitnessChallenge;
use App\Models\FitnessChallengeWeekDay;
use App\Models\ChallengePackages;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendNewChallengeNotificationJob;

class FitnessChallengeController extends Controller
{
    use Common_trait;

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $page = $request->input('page', 1);

        $query = FitnessChallenge::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('challenge_name', 'LIKE', '%' . $search . '%')
                    ->orWhere('goal', 'LIKE', '%' . $search . '%')
                    ->orWhere('duration_weeks', 'LIKE', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Prevent showing empty pages
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.fitnessChallengeIndex', ['limit' => $limit]);
        }

        $fitnessChallenges = $query->orderBy('id', 'desc')->paginate($limit);

        // Append parameters to pagination
        $fitnessChallenges->appends([
            'search' => $search,
            'limit' => $limit,
        ]);

        return view('admin.fitness-challenge.index', compact('fitnessChallenges'));
    }


    public function fitnessChallengeAdd()
    {
        $plans = ChallengePackages::whereNull('deleted_at')->pluck('amount', 'id');
        $exercises = Exercise::whereNull('deleted_at')->get();
        return view('admin.fitness-challenge.add', compact('plans', 'exercises'));
    }


    // Store the fitness challenge
    public function fitnessChallengeSave(Request $request)
    {
        // Validate the basic form fields
        $validator = Validator::make($request->all(), [
            'challenge_name' => 'required|string|max:100',
            'goal' => 'required|string|max:255',
            'duration_weeks' => 'required|integer|min:1',
            'plan_id' => 'required|exists:ft_challenge_packages,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required|string|max:1200',
            'weeks_data' => 'required|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Handle the image upload
        if ($request->hasFile('image')) {
            $imagePath = $this->file_upload($request->file('image'), 'fitness_challenge/images');
        }

        // Create the fitness challenge
        $challenge = FitnessChallenge::create([
            'challenge_name' => Str::title(preg_replace('/\s+/', ' ', trim($request->challenge_name))),
            'goal' => $request->goal,
            'duration_weeks' => $request->duration_weeks,
            'plan_id' => $request->plan_id,
            'image' => $imagePath,
            'description' => $request->description,
        ]);

        // Process the weeks data
        $weeksData = json_decode($request->weeks_data, true);
        //     echo '<pre>';
        //    print_r($weeksData);die();
        foreach ($weeksData as $weekData) {
            foreach ($weekData['days'] as $dayData) {
                $day = FitnessChallengeWeekDay::create([
                    'week' => $weekData['week'],
                    'day_number' => $dayData['day'],
                    'fitness_challenge_id' => $challenge->id,
                    'is_rest_day' => $dayData['is_rest_day'],
                ]);

                if (!$dayData['is_rest_day'] && !empty($dayData['exercises'])) {
                    foreach ($dayData['exercises'] as $exerciseData) {
                        // print_r($exerciseData['body_parts']);die();
                        FitnessChallengeExercise::create([
                            'fitness_challenges_week_days_id' => $day->id,
                            'exercise_id' => $exerciseData['exercise_id'],
                            'day_id' => $dayData['day'],
                            'reps' => $exerciseData['reps'],
                            'sets' => $exerciseData['sets'],
                            'order' => $exerciseData['order'],
                            'rest_time' => $exerciseData['rest_time'],
                        ]);
                    }
                }
            }
        }

        User::whereNotNull('device_id')
            ->where('notifications_enabled', 1)
            ->chunk(100, function ($users) use ($challenge) {
                foreach ($users as $user) {
                    $data = [
                        'deviceToken' => $user->device_id,
                        'type'        => 'is_challenge',
                        'message'     => 'A new challenge "' . $challenge->challenge_name . '" is available. Join now!',
                        'item'        => 'Challenge Reminder',
                        'id'          => $challenge->id,
                        'user_id'     => $user->id,
                    ];

                    SendNewChallengeNotificationJob::dispatch($data);
                }
            });

        return redirect()->route('admin.fitnessChallengeIndex')->with('toastr', [
            'type' => 'success',
            'message' => 'A new fitness challenge added successfully!'
        ]);
    }

    public function getExerciseDetails(Request $request)
    {
        try {
            $exercise = Exercise::with('bodyType')->findOrFail($request->id);

            return response()->json([
                'success' => true,
                'exercise' => [
                    'id' => $exercise->id,
                    'exercise_name' => $exercise->exercise_name,
                    'level' => $exercise->level,
                    'location' => $exercise->location,
                    'body_part' => $exercise->bodyType->name ?? '', // Get body part from BodyType model
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exercise not found'
            ], 404);
        }
    }

    public function fitnessChallengeEdit($id)
    {
        $challenge = FitnessChallenge::with([
            'weekDays' => function ($query) {
                $query->orderBy('week', 'asc')
                    ->orderBy('day_number', 'asc');
            },
            'weekDays.exercises' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'weekDays.exercises.exercise.bodyType'
        ])->findOrFail($id);

        // Group days by week for easier display
        $weeks = [];
        foreach ($challenge->weekDays as $day) {
            $weeks[$day->week]['days'][] = $day;
        }
        //     echo '<pre>';
        //    print_r($challenge);die();
        $plans = ChallengePackages::pluck('plan_name', 'id');
        $exercises = Exercise::with('bodyType')->get();
        $bodyTypes = BodyType::pluck('name', 'id');

        return view('admin.fitness-challenge.edit', compact(
            'challenge',
            'weeks',
            'plans',
            'exercises',
            'bodyTypes'
        ));
    }

    public function fitnessChallengeUpdate(Request $request, $id)
    {
        //  echo '<pre>';
        // print_r($request->all());die();
        // Validate the basic form fields
        $validator = Validator::make($request->all(), [
            'challenge_name' => 'required|string|max:100',
            'goal' => 'required|string|max:55',
            'duration_weeks' => 'required|integer|min:1',
            'plan_id' => 'required|exists:ft_challenge_packages,id',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required|string|max:1200',
            'weeks_data' => 'required|json',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the challenge to update
        $challenge = FitnessChallenge::findOrFail($id);

        // Handle the image upload
        $imagePath = $challenge->image; // Keep existing image if not updated
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($challenge->image && file_exists(public_path($challenge->image))) {
                unlink(public_path($challenge->image));
            }
            $imagePath = $this->file_upload($request->file('image'), 'fitness_challenge/images');
        }

        // Update the fitness challenge
        $challenge->update([
            'challenge_name' => Str::title(preg_replace('/\s+/', ' ', trim($request->challenge_name))),
            'goal' => $request->goal,
            'duration_weeks' => $request->duration_weeks,
            'plan_id' => $request->plan_id,
            'image' => $imagePath,
            'description' => $request->description,
        ]);

        // Delete existing weeks and days for this challenge
        $weekDays = FitnessChallengeWeekDay::where('fitness_challenge_id', $challenge->id)->get();
        foreach ($weekDays as $weekDay) {
            // Delete associated exercises
            FitnessChallengeExercise::where('fitness_challenges_week_days_id', $weekDay->id)->delete();
        }
        // Delete the week days
        FitnessChallengeWeekDay::where('fitness_challenge_id', $challenge->id)->delete();

        // Process the weeks data
        $weeksData = json_decode($request->weeks_data, true);
        // echo '<pre>';
        // print_r($weeksData);die();

        foreach ($weeksData as $weekData) {
            foreach ($weekData['days'] as $dayData) {
                $day = FitnessChallengeWeekDay::create([
                    'week' => $weekData['week'],
                    'day_number' => $dayData['day'],
                    'fitness_challenge_id' => $challenge->id,
                    'is_rest_day' => $dayData['is_rest_day'],
                ]);

                if (!$dayData['is_rest_day'] && !empty($dayData['exercises'])) {
                    foreach ($dayData['exercises'] as $exerciseData) {
                        FitnessChallengeExercise::create([
                            'fitness_challenges_week_days_id' => $day->id,
                            'exercise_id' => $exerciseData['exercise_id'],
                            'day_id' => $dayData['day'],
                            'reps' => $exerciseData['reps'],
                            'sets' => $exerciseData['sets'],
                            'rest_time' => $exerciseData['rest_time'] ?? null,
                            'order' => $exerciseData['order'] ?? 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.fitnessChallengeIndex')->with('toastr', [
            'type' => 'success',
            'message' => 'Fitness challenge updated successfully!'
        ]);
    }


    public function fitnessChallengeDelete($id)
    {
        try {
            DB::beginTransaction();

            $challenge = FitnessChallenge::findOrFail($id);

            // Delete related exercises
            $weekDays = FitnessChallengeWeekDay::where('fitness_challenge_id', $id)->get();
            foreach ($weekDays as $weekDay) {
                FitnessChallengeExercise::where('fitness_challenges_week_days_id', $weekDay->id)->delete();
            }

            // Delete week days
            FitnessChallengeWeekDay::where('fitness_challenge_id', $id)->delete();

            // Delete the challenge
            $challenge->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fitness challenge and all related data deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error deleting challenge: ' . $e->getMessage()
            ], 500);
        }
    }
}
