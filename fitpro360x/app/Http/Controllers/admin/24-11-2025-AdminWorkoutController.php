<?php

namespace App\Http\Controllers\admin;

use App\Models\Admin_Meal_Entries;
use App\Models\MealDietPreference;
use App\Traits\HasSearch;
use App\Http\Controllers\Controller;
use App\Models\AdminWorkoutSettings;
use App\Models\Exercise;
use App\Models\BodyType;
use App\Models\UserWorkoutPlan;
use App\Models\MealsPlan;
use Illuminate\Support\Facades\Log;
use App\Models\SubscriptionPackages;
use App\Models\Question;
use App\Models\WorkoutWeek;
use Illuminate\Http\Request;
use App\Traits\Common_trait;
use Illuminate\Support\Facades\File; // Add at top if not already
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Workout_Programs;
use App\Models\Workout_Week_Days;
use App\Models\Workout_Program_Exercises;


class AdminWorkoutController extends Controller
{
    use Common_trait;

    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $page = $request->input('page', 1);

        $query = Workout_Programs::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('goal', 'LIKE', '%' . $search . '%')
                    ->orWhere('duration_weeks', 'LIKE', '%' . $search . '%');
            });
        }

        $total = $query->count();
        $maxPage = ceil($total / $limit);

        // Prevent showing empty pages
        if ($page > 1 && $page > $maxPage && $total > 0) {
            return redirect()->route('admin.workoutPlansIndex', ['limit' => $limit]);
        }

        $workout = $query->orderBy('id', 'desc')->paginate($limit);

        // Append parameters to pagination
        $workout->appends([
            'search' => $search,
            'limit' => $limit,
        ]);

        return view('admin.workout-plan.index', compact('workout'));
    }
    public function workoutPlansAdd()
    {
        $exercises = Exercise::all(); // Ensure Exercise model exists
        return view('admin.workout-plan.add', compact('exercises'));
    }


    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            //'goal' => 'required|string|max:255',
            'duration_weeks' => 'required|integer|min:1',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'level' => 'required|in:1,2,3',
            // 'location' => 'required|in:1,2',
            'description' => 'required|string',
            'weeks_data' => 'required|json'
        ]);

        // Decode the weeks data
        $weeksData = json_decode($request->weeks_data, true);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->file_upload($request->file('image'), 'workout/images');
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the main workout program
            $workoutProgram = Workout_Programs::create([
                'title' => $request->title,
                //'goal' => $request->goal,
                'duration_weeks' => $request->duration_weeks,
                'image' => $imagePath,
                'level' => $request->level,
                // 'location' => $request->location,
                'description' => $request->description,
                'status' => 1 // Assuming active status
            ]);

            //   $weeksData = json_decode($request->weeks_data, true);
            // Process each week

            foreach ($weeksData as $week) {
                // Process each day in the week

                foreach ($week['days'] as $day) {
                    // Create the week day record
                    $weekDay = Workout_Week_Days::create([
                        'week' => $week['week'],
                        'day_number' => $day['day'],
                        'workout_program_id' => $workoutProgram->id,
                        'is_rest_day' => $day['is_rest_day'] ? 1 : 0
                    ]);

                    // If it's not a rest day, add exercises
                    if (!$day['is_rest_day'] && !empty($day['exercises'])) {
                        foreach ($day['exercises'] as $exercise) {
                            Workout_Program_Exercises::create([
                                'workout_week_days_id' => $weekDay->id,
                                'exercise_id' => $exercise['exercise_id'],
                                'day_id' => $day['day'],
                                'sets' => $exercise['sets'],
                                'reps' => $exercise['reps'],
                                'rest_seconds' => $exercise['rest_time'],
                                'order' => $exercise['order']
                            ]);
                        }
                    }
                }
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('admin.workout', ['program_id' => $workoutProgram->id])
                ->with('success');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Error creating workout program: ' . $e->getMessage());
        }
    }

    public function workout()
    {
        $questions = Question::where('showing_in', 1) // 1 = Web only
            ->orWhere('showing_in', 3) // 3 = Both
            ->whereNotNull('type_for_web')
            ->orderBy('question_order_for_web')
            ->with('options')
            ->get();

        $workoutProgram = Workout_Programs::latest()->first();
        $programId  = $workoutProgram->id;


        return view('admin.workout-plan.workout-settings-add', compact('questions', 'programId'));
    }

    public function workoutupdate(Request $request, $programId = '')
    {
        $program = Workout_Programs::findOrFail($programId);
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required', // each response must be selected (can be array or string)
        ]);

        foreach ($validated['responses'] as $questionId => $optionOrOptions) {
            $question = Question::find($questionId);
            if (!$question) continue;

            switch ($question->type_for_web) {
                case 1: // Single choice (radio)
                    if (!is_array($optionOrOptions)) {
                        AdminWorkoutSettings::create([
                            'workout_program_id' => $program->id,
                            'question_id' => $questionId,
                            'option_id' => $optionOrOptions,
                            'created_at' => now(),
                        ]);
                    }
                    break;

                case 2: // Multiple choice (checkbox)
                    if (is_array($optionOrOptions)) {
                        foreach ($optionOrOptions as $optionId) {
                            AdminWorkoutSettings::create([
                                'workout_program_id' => $program->id,
                                'question_id' => $questionId,
                                'option_id' => $optionId,
                                'created_at' => now(),
                            ]);
                        }
                    }
                    break;

                default:
                    // Optional fallback logic
                    AdminWorkoutSettings::create([
                        'workout_program_id' => $program->id,
                        'question_id' => $questionId,
                        'option_id' => null,
                        'created_at' => now(),
                    ]);
                    break;
            }
        }
        return redirect()->route('admin.meal', $programId)->with('success');
    }

    public function meal($programId = '')
    {
        // Get all unique diet preferences and types
        $dietPreferences = MealsPlan::select('diet_preference')->distinct()->pluck('diet_preference');
        $mealTypes = MealsPlan::select('type')->distinct()->pluck('type');

        $mealDietPreferences = MealDietPreference::get();

        $typeLabels = [
            1 => 'Breakfast',
            2 => 'Lunch',
            3 => 'Dinner'
        ];

        // Get all meal titles grouped by diet and type
        $mealOptions = [];
        foreach ($dietPreferences as $diet) {
            foreach ($mealTypes as $type) {
                $mealOptions[$diet][$type] = MealsPlan::where('diet_preference', $diet)
                    ->where('type', $type)
                    ->pluck('title')
                    ->toArray();
            }
        }
        return view('admin.workout-plan.meal', compact(
            'dietPreferences',
            'mealTypes',
            'typeLabels',
            'mealOptions',
            'programId',
            'mealDietPreferences'
        ));
    }

    public function getMealsByPreference(Request $request)
    {
        $dietId = $request->diet_preference;

        $meals = MealsPlan::where('diet_preference', $dietId)
            ->select('id', 'title', 'type') // 1=Breakfast, 2=Lunch, 3=Dinner
            ->get()
            ->groupBy('type');
        return response()->json($meals);
    }

    public function renderMealSection(Request $request)
    {
        $index = $request->index;
        $mealDietPreferences = MealDietPreference::get();

        return response()->json([
            'html' => view('components.admin-workout-meal', compact('index', 'mealDietPreferences'))->render()
        ]);
    }

    // public function saveUserMeals(Request $request, $programId = '')
    // {
    //     //  pree($request->all());
    //     // $existing = Admin_Meal_Entries::where('workout_program_id', $programId)->exists();

    //     // if ($existing) {
    //     //     return redirect()->back()->with('error', 'Meals are already set for this workout program.');
    //     // }

    //     // $meals = $request->input('meal', []);

    //     // if (empty($meals) || !is_array($meals)) {
    //     //     return redirect()->back()->with('error', 'Please add at least one meal entry before submitting.');
    //     // }

    //     // $hasValidMeal = false;

    //     // foreach ($meals as $mealGroup) {
    //     //     $dietPreference = $mealGroup['diet_preference'] ?? null;

    //     //     if (!$dietPreference) continue;

    //     //     foreach (['breakfast', 'lunch', 'dinner'] as $type) {
    //     //         if (!isset($mealGroup[$type]) || empty($mealGroup[$type])) continue;

    //     //         foreach ($mealGroup[$type] as $mealId) {
    //     //             if (!$mealId) continue;

    //     //             $data = new Admin_Meal_Entries();
    //     //             $data->workout_program_id = $programId;
    //     //             $data->meal_id = $mealId;
    //     //             $data->diet_preference = $dietPreference;
    //     //             $data->save();

    //     //             $hasValidMeal = true;
    //     //         }
    //     //     }
    //     // }
    //     foreach ($request->meal as $mealEntry) {
    //     $dietPreferences = explode(',', $mealEntry['diet_preference']);

    //     foreach ($dietPreferences as $dietPreference) {
    //         if (!empty($mealEntry['breakfast'])) {
    //             foreach ($mealEntry['breakfast'] as $mealId) {
    //                 DB::table('ft_admin_meal_entries')->insert([
    //                     'workout_program_id' => $programId,
    //                     'meal_id' => $mealId,
    //                     'diet_preference' => $dietPreference,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             }
    //         }

    //         // Repeat for lunch and dinner
    //         if (!empty($mealEntry['lunch'])) {
    //             foreach ($mealEntry['lunch'] as $mealId) {
    //                 // Same insert logic
    //             }
    //         }

    //         if (!empty($mealEntry['dinner'])) {
    //             foreach ($mealEntry['dinner'] as $mealId) {
    //                 // Same insert logic
    //             }
    //         }
    //     }
    // }

    //     // if (!$hasValidMeal) {
    //     //     return redirect()->back()->with('error', 'Please select at least one meal (Breakfast, Lunch, or Dinner).');
    //     // }

    //     return redirect()->route('admin.workoutPlansIndex')->with('success', 'Workout plan created successfully.');
    // }
    public function saveUserMeals(Request $request, $programId = '')
    {
        // Validate the program ID
        if (empty($programId)) {
            return redirect()->back()->with('error', 'Invalid workout program');
        }

        // Validate the request data
        $validated = $request->validate([
            'meal.*.diet_preference' => 'required|string',
            'meal.*.breakfast' => 'nullable|array',
            'meal.*.breakfast.*' => 'integer|exists:ft_meal_plans,id',
            'meal.*.lunch' => 'nullable|array',
            'meal.*.lunch.*' => 'integer|exists:ft_meal_plans,id',
            'meal.*.dinner' => 'nullable|array',
            'meal.*.dinner.*' => 'integer|exists:ft_meal_plans,id',
        ]);

        // Clear existing meals for this program
        DB::table('ft_admin_meal_entries')
            ->where('workout_program_id', $programId)
            ->delete();

        // Track if we save any meals
        $hasValidMeal = false;

        // Process each meal entry
        foreach (($request->meal) as $mealEntry) {
            // Handle comma-separated diet preferences
            $dietPreferences = explode(',', $mealEntry['diet_preference']);



            $isMixed = is_array($dietPreferences) && count($dietPreferences) > 1;

            $dietPreferenceIdToUse = $isMixed ? 5 : (int) $dietPreferences[0]; // FOR MIXED DIET PREFERENCES

            foreach ($dietPreferences as $dietPreference) {
                $dietPreference = trim($dietPreference);

                if (!is_numeric($dietPreference)) {
                    continue; // Skip invalid diet preferences
                }

                foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                    if (!empty($mealEntry[$mealType])) {
                        foreach ($mealEntry[$mealType] as $mealId) {
                            // Avoid duplicate meal insert in mixed
                            $key = $programId . '-' . $mealId . '-' . $dietPreferenceIdToUse;
                            if (isset($alreadyInserted[$key])) {
                                continue;
                            }
                
                            DB::table('ft_admin_meal_entries')->insert([
                                'workout_program_id' => $programId,
                                'meal_id' => $mealId,
                                'diet_preference' => $dietPreferenceIdToUse,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $alreadyInserted[$key] = true;
                            $hasValidMeal = true;
                        }
                    }
                }

                // Process breakfast meals
                // if (!empty($mealEntry['breakfast'])) {
                //     foreach ($mealEntry['breakfast'] as $mealId) {
                //         DB::table('ft_admin_meal_entries')->insert([
                //             'workout_program_id' => $programId,
                //             'meal_id' => $mealId,
                //             'diet_preference' => $dietPreferenceIdToUse,
                //             'created_at' => now(),
                //             'updated_at' => now(),
                //         ]);
                //         $hasValidMeal = true;
                //     }
                // }

                // // Process lunch meals
                // if (!empty($mealEntry['lunch'])) {
                //     foreach ($mealEntry['lunch'] as $mealId) {
                //         DB::table('ft_admin_meal_entries')->insert([
                //             'workout_program_id' => $programId,
                //             'meal_id' => $mealId,
                //             'diet_preference' => $dietPreferenceIdToUse,
                //             'created_at' => now(),
                //             'updated_at' => now(),
                //         ]);
                //         $hasValidMeal = true;
                //     }
                // }

                // // Process dinner meals
                // if (!empty($mealEntry['dinner'])) {
                //     foreach ($mealEntry['dinner'] as $mealId) {
                //         DB::table('ft_admin_meal_entries')->insert([
                //             'workout_program_id' => $programId,
                //             'meal_id' => $mealId,
                //             'diet_preference' => $dietPreferenceIdToUse,
                //             'created_at' => now(),
                //             'updated_at' => now(),
                //         ]);
                //         $hasValidMeal = true;
                //     }
                // }
            }
        }

        if (!$hasValidMeal) {
            return redirect()->back()->with('error', 'Please select at least one meal (Breakfast, Lunch, or Dinner).');
        }

        return redirect()->route('admin.workoutPlansIndex')->with('success', 'Meals saved successfully.');
    }

    public function workoutPlansDelete($id)
    {
        try {
            // Check if this workout plan is used by any user
            $userEntryExists = UserWorkoutPlan::where('workout_program_id', $id)->exists();

            if ($userEntryExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete this workout plan. It is currently assigned to one or more users.'
                ], 400);
            }

            DB::beginTransaction();

            $workout = Workout_Programs::findOrFail($id);

            // Delete related exercises
            $weekDays = Workout_Week_Days::where('workout_program_id', $id)->get();
            foreach ($weekDays as $weekDay) {
                Workout_Program_Exercises::where('workout_week_days_id', $weekDay->id)->delete();
            }

            // Delete week days
            Workout_Week_Days::where('workout_program_id', $id)->delete();

            // Delete the workout program itself
            $workout->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workout plan and all related data deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // return response()->json([
            //     'success' => false,
            //     'message' => 'Error deleting workout plan: ' . $e->getMessage()
            // ], 500);
        }
    }



    public function workoutPlansEdit($id)
    {
        $workout = Workout_Programs::with([
            'weeks' => function ($query) {
                $query->orderBy('week', 'asc')
                    ->orderBy('day_number', 'asc');
            },
            'weeks.exercises' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'weeks.exercises.exercise.bodyType'
        ])->findOrFail($id);

        // Group days by week for easier display
        $weeks = [];
        foreach ($workout->days as $day) {
            $weeks[$day->week]['days'][] = $day;
        }

        $plans = SubscriptionPackages::pluck('plan_name', 'id');
        $exercises = Exercise::with('bodyType')->get();

        // pree($workout);


        return view('admin.workout-plan.edit', compact(
            'workout',
            'weeks',
            'plans',
            'exercises',

        ));
    }


    public function workoutPlansUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            //'goal' => 'required|string|max:255',
            'duration_weeks' => 'required|integer|min:1',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
            'level' => 'required|in:1,2,3',
            // 'location' => 'required|in:1,2',
            'description' => 'required|string',
            'weeks_data' => 'required|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $workout = Workout_Programs::findOrFail($id);
        $imagePath = $workout->image; // Keep existing image if not updated
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($workout->image && file_exists(public_path($workout->image))) {
                unlink(public_path($workout->image));
            }
            $imagePath = $this->file_upload($request->file('image'), 'exercise/images');
        }

        $action = $request->input('action');

        // Update the fitness challenge
        $workout->update([
            'title' => $request->title,
            //'goal' => $request->goal,
            'duration_weeks' => $request->duration_weeks,
            'image' => $imagePath,
            'level' => $request->level,
            // 'location' => $request->location,
            'description' => $request->description,
            'status' => 1
        ]);

        // Delete existing weeks and days for this challenge
        $weekDays = Workout_Week_Days::where('workout_program_id', $workout->id)->get();
        foreach ($weekDays as $weekDay) {
            // Delete associated exercises
            Workout_Program_Exercises::where('workout_week_days_id', $weekDay->id)->delete();
        }
        // Delete the week days
        Workout_Week_Days::where('workout_program_id', $workout->id)->delete();

        // Process the weeks data
        $weeksData = json_decode($request->weeks_data, true);
        // echo '<pre>';
        // print_r($weeksData);die();

        foreach ($weeksData as $weekData) {
            foreach ($weekData['days'] as $dayData) {
                $day = Workout_Week_Days::create([
                    'week' => $weekData['week'],
                    'day_number' => $dayData['day'],
                    'workout_program_id' => $workout->id,
                    'is_rest_day' => $dayData['is_rest_day'],
                ]);

                if (!$dayData['is_rest_day'] && !empty($dayData['exercises'])) {
                    foreach ($dayData['exercises'] as $exerciseData) {
                        Workout_Program_Exercises::create([
                            'workout_week_days_id' => $day->id,
                            'exercise_id' => $exerciseData['exercise_id'],
                            'day_id' => $dayData['day'],
                            'reps' => $exerciseData['reps'],
                            'sets' => $exerciseData['sets'],
                            'rest_seconds' => $exerciseData['rest_time'] ?? null,
                            'order' => $exerciseData['order'] ?? 0,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.workoutSettingsEdit', $id)->with('success', 'Saved and moved to next step!');

            // if ($action === 'next') {
            //     return redirect()->route('admin.workoutSettingsEdit', $id)->with('error', 'Workout plan updated successfully');
            // }

            // return redirect()->back()->with('success', 'Saved and moved to next step!');
        ;
    }

    public function view($id)
    {
        $workout = Workout_Programs::findOrFail($id);
        return view('admin.workout-plan.view', compact('workout'));
    }

    public function delete($id)
    {
        $allInfo = Workout_Programs::findOrFail($id);

        if ($allInfo->delete()) {
            return redirect()->back()->with('flash-success', 'Workout Plan deleted successfully.');
        }

        return redirect()->back()->with('flash-error', 'Failed to delete the Workout Plan.');
    }

    public function workoutSettingsEdit($workoutProgramId = '')
    {
        $workoutProgram = Workout_Programs::findOrFail($workoutProgramId);

        $questions = Question::where('showing_in', 1) // 1 = Web only
            ->orWhere('showing_in', 3) // 3 = Both
            ->whereNotNull('type_for_web')
            ->orderBy('question_order_for_web')
            ->with('options')
            ->get();

        $workoutSettings = AdminWorkoutSettings::where('workout_program_id', $workoutProgramId)->get();

        $savedResponses = $workoutSettings->groupBy('question_id')->map(function ($items) {
            return $items->pluck('option_id')->toArray();
        })->toArray();

        return view('admin.workout-plan.workout-settings-edit', compact('questions', 'workoutProgramId', 'savedResponses'));
    }

    public function workoutSettingsUpdate(Request $request, $workoutProgramId)
    {
        $action = $request->input('action');

        Workout_Programs::findOrFail($workoutProgramId);

        $responses = $request->input('responses', []);

        $existingSettings = AdminWorkoutSettings::where('workout_program_id', $workoutProgramId)->get();

        foreach ($responses as $questionId => $optionIds) {
            $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];

            $existingOptionIds = $existingSettings
                ->where('question_id', $questionId)
                ->pluck('option_id')
                ->toArray();


            $toAdd = array_diff($optionIds, $existingOptionIds);

            $toDelete = array_diff($existingOptionIds, $optionIds);

            foreach ($toAdd as $optionId) {
                AdminWorkoutSettings::create([
                    'workout_program_id' => $workoutProgramId,
                    'question_id' => $questionId,
                    'option_id' => $optionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!empty($toDelete)) {
                AdminWorkoutSettings::where('workout_program_id', $workoutProgramId)
                    ->where('question_id', $questionId)
                    ->whereIn('option_id', $toDelete)
                    ->delete();
            }
        }

        $answeredQuestionIds = array_keys($responses);


        $allQuestionIds = $existingSettings->pluck('question_id')->unique()->toArray();
        $questionsToClear = array_diff($allQuestionIds, $answeredQuestionIds);


        if (!empty($questionsToClear)) {
            AdminWorkoutSettings::where('workout_program_id', $workoutProgramId)
                ->whereIn('question_id', $questionsToClear)
                ->delete();
        }

        if ($action === 'next') {
            return redirect()->route('admin.editWorkoutMeal', $workoutProgramId)->with('success', 'Saved and moved to next step!');
        }

        return redirect()->back()->with('success');
    }

    public function show($slug)
    {
        $preference = MealDietPreference::where('slug', $slug)->firstOrFail();

        return response()->json([
            'id' => $preference->id,
            'name' => $preference->name,
            'slug' => $preference->slug
        ]);
    }


    public function editWorkoutMeal(Request $request, $programId = '')
    {
        // Get all meal entries for this program
        $entries = Admin_Meal_Entries::with('meal')
            ->where('workout_program_id', $programId)
            ->get();

        if ($entries->isEmpty()) {
            return redirect()->route('admin.meal', $programId)
                ->with('success', 'Saved and moved to next step!');
        }
        // pree($entries);
        // Group meals by diet preference and meal type, only for meals present in $entries
        $normalizedMeals = [];

        foreach ($entries as $entry) {
            $dietPrefIds = explode(',', $entry->diet_preference);
            $mealType = $entry->meal->type ?? null;

            if (!$mealType) continue;

            foreach ($dietPrefIds as $dietPrefId) {
                $dietPrefId = trim($dietPrefId);
                if (!is_numeric($dietPrefId)) continue;

                if (!isset($normalizedMeals[$dietPrefId])) {
                    $normalizedMeals[$dietPrefId] = [
                        'diet_preference' => $dietPrefId,
                        'breakfast' => [],
                        'lunch' => [],
                        'dinner' => [],
                    ];
                }

                switch ($mealType) {
                    case 1:
                        $normalizedMeals[$dietPrefId]['breakfast'][] = $entry->meal_id;
                        break;
                    case 2:
                        $normalizedMeals[$dietPrefId]['lunch'][] = $entry->meal_id;
                        break;
                    case 3:
                        $normalizedMeals[$dietPrefId]['dinner'][] = $entry->meal_id;
                        break;
                }
            }
        }
        $groupedMeals = $normalizedMeals;

        // pree($groupedMeals);
        // Only include diet preferences and meals present in $entries
        $mealDietPreferences = MealDietPreference::whereIn('id', array_keys($groupedMeals))->get();
        $allMeals = MealsPlan::whereIn('id', $entries->pluck('meal_id'))->get()->keyBy('id');



        return view('admin.workout-plan.edit-meal', [
            'programId' => $programId,
            'mealDietPreferences' => $mealDietPreferences,
            'groupedMeals' => $groupedMeals,
            'allMeals' => $allMeals
        ]);
    }

    public function updateWorkoutMeal(Request $request, $programId = '')
    {
        $inputMeals = $request->input('meal', []);
        $newMeals = [];

        // Loop through each meal group to validate diet preference & meals
        foreach ($inputMeals as $group) {
            $dietPreferenceRaw = $group['diet_preference'] ?? null;
            if (!$dietPreferenceRaw) {
                return redirect()->back()->with('error', 'Please select at least one meal (Breakfast, Lunch, or Dinner).');
            }

            $breakfast = $group['breakfast'] ?? [];
            $lunch = $group['lunch'] ?? [];
            $dinner = $group['dinner'] ?? [];

            if (empty($breakfast) && empty($lunch) && empty($dinner)) {
                return redirect()->back()->with('error', 'Please select at least one meal (Breakfast, Lunch, or Dinner).');
            }

            // Support comma-separated diet preferences
            $dietPreferences = explode(',', $dietPreferenceRaw);
            foreach ($dietPreferences as $dietPreference) {
                $dietPreference = trim($dietPreference);
                if (!is_numeric($dietPreference)) {
                    continue;
                }
                foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
                    $meals = $group[$mealType] ?? [];
                    foreach ($meals as $mealId) {
                        if ($mealId) {
                            $newMeals[] = [
                                'meal_id' => $mealId,
                                'diet_preference' => $dietPreference,
                            ];
                        }
                    }
                }
            }
        }

        // Fetch existing meals for the current program
        $existingMeals = Admin_Meal_Entries::where('workout_program_id', $programId)
            ->get()
            ->map(function ($item) {
                return [
                    'meal_id' => $item->meal_id,
                    'diet_preference' => $item->diet_preference
                ];
            })
            ->toArray();

        // Find meals that were removed (not in newMeals)
        $toDelete = [];
        foreach ($existingMeals as $existingMeal) {
            $mealFound = false;

            // Check if the existing meal is in the newMeals (form submission)
            foreach ($newMeals as $newMeal) {
                if ($existingMeal['meal_id'] == $newMeal['meal_id'] && $existingMeal['diet_preference'] == $newMeal['diet_preference']) {
                    $mealFound = true;
                    break;
                }
            }

            // If the meal is not in the new form, mark it for deletion
            if (!$mealFound) {
                $toDelete[] = $existingMeal;
            }
        }

        // Delete the meals that are no longer in the form submission
        foreach ($toDelete as $entry) {
            Admin_Meal_Entries::where('workout_program_id', $programId)
                ->where('meal_id', $entry['meal_id'])
                ->where('diet_preference', $entry['diet_preference'])
                ->delete();
        }

        // Now insert new meals that aren't in the existing meals list
        foreach ($newMeals as $newMeal) {
            $mealExists = false;

            // Check if the meal already exists in the database for this workout program
            foreach ($existingMeals as $existingMeal) {
                if ($existingMeal['meal_id'] == $newMeal['meal_id'] && $existingMeal['diet_preference'] == $newMeal['diet_preference']) {
                    $mealExists = true;
                    break;
                }
            }

            // If the meal doesn't exist, insert it
            if (!$mealExists) {
                Admin_Meal_Entries::create([
                    'workout_program_id' => $programId,
                    'meal_id' => $newMeal['meal_id'],
                    // 'diet_preference' => $newMeal['diet_preference']
                    'diet_preference' => (string) $newMeal['diet_preference']
                ]);
            }
        }

        return redirect()->route('admin.workoutPlansIndex')->with('success', 'Workout plan updated successfully');
    }
}
