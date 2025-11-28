<?php

namespace App\Http\Controllers\api\v1;

use App\Models\User;
use App\Models\QuestionAnswerUser;
use App\Models\Question;
use App\Models\QuestionsOption;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\UserWorkoutPlan;
use App\Models\Admin_Meal_Entries;
use App\Models\MealsPlan;
use Illuminate\Support\Facades\Auth;

class MealsController extends BaseApiController
{
    public function getDietPreference(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError(
                'Unauthorized',
                null
            );
        }

        $questionId = 9;

        // Fetch all options for the question
        $question = Question::with(['options' => function ($query) {
            $query->select('id', 'question_id', 'label_for_app', 'instruction');
        }])->find($questionId);

        if (!$question || $question->options->isEmpty()) {
            return $this->sendError(
                'Question or its options not found.',
                null
            );
        }
        $selectedOptionIds = QuestionAnswerUser::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->pluck('option_id')
            ->toArray();

        $optionToDietPreferenceMap = [
            30 => 1, // Vegan
            28 => 2, // Veg
            29 => 3, // Non-Veg
            31 => 4, // Keto

        ];

        $options = $question->options->map(function ($option) use ($selectedOptionIds, $optionToDietPreferenceMap) {
            return [
                'id' => $option->id,
                'label_for_app' => $option->label_for_app,
                'instruction' => $option->instruction,
                'selected' => in_array($option->id, $selectedOptionIds),
                'diet_preference_id' => $optionToDietPreferenceMap[$option->id],
            ];
        });

        return $this->sendResponse(
            [
                'options' => $options
            ],
            'Diet preferences retrieved successfully.',
            200
        );
    }
    public function updateDietPreference(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError(
                'Unauthorized',
                null
            );
        }

        $questionId = 9;

        $validated = Validator::make($request->all(), [
            'option_id' => 'required|exists:ft_question_options,id',
        ]);

        if ($validated->fails()) {
            return $this->sendError(
                $validated->errors()->first(),
                null
            );
        }

        $optionId = $request->input('option_id');

        $option = QuestionsOption::where('id', $optionId)
            ->where('question_id', $questionId)
            ->first();

        if (!$option) {
            return $this->sendError(
                'Invalid option for this question.',
                null
            );
        }
        QuestionAnswerUser::updateOrCreate(
            [
                'user_id' => $user->id,
                'question_id' => $questionId,
            ],
            [
                'option_id' => $optionId,
            ]
        );

        return $this->sendResponse(
            null,
            'Diet preference updated successfully.',
            200
        );
    }


    public function detailMeal(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError(
                'Unauthorized',
                null
            );
        }

        $validator = Validator::make($request->all(), [
            'meal_id' => 'required|exists:ft_meal_plans,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                $validator->errors()->first(),
                null
            );
        }

        $mealid = $request->input('meal_id');

        $meal = MealsPlan::find($mealid);

        if (!$meal) {
            return $this->sendError(
                'Meal not found.',
                null
            );
        }

        $type = $meal->type;
        $dietPreference = $meal->diet_preference;

        $userWorkoutPlan = UserWorkoutPlan::where('user_id', $user->id)->first();

        if (!$userWorkoutPlan) {
            return $this->sendError(
                'No workout plan found for the user.',
                null
            );
        }

        $mealEntries = Admin_Meal_Entries::with('meal')
            ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->whereHas('meal', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            })
            ->whereHas('meal', function ($q) use ($dietPreference) {
                $q->where('diet_preference', $dietPreference);
            })
            ->whereHas('meal', function ($q) use ($mealid) {
                $q->where('id', $mealid);
            })
            ->get()
            ->unique('meal_id') 
           ->values();

        if ($mealEntries->isEmpty()) {
            return $this->sendError(
                'No meals found for this type and diet preference.',
                null
            );
        }

        $meals = $mealEntries->map(function ($entry) {
            $meal = $entry->meal;

            return [
                'meal_id' => $meal->id,
                'title' => $meal->title,
                'image' => $meal->image ? url($meal->image) : null,
                'description' => $meal->description,
                'ingredients' => collect($meal->ingredients)->map(function ($ingredient) {
                    return collect($ingredient)->except(['created_at', 'deleted_at', 'updated_at']);
                })->values(),
                'nutritions' => [
                    'protein' => $meal->protein,
                    'carbs' => $meal->carbs,
                    'fat' => $meal->fat,
                ],
            ];
        });

        return $this->sendResponse([
            'meals' => $meals,
        ], 'Meals fetched successfully.', 200);
    }

    public function getMealByTypeAndDietPreference(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized', null, 401);
        }

        // Validate meal type input
        $validator = Validator::make($request->all(), [
            'type' => 'required|integer|in:1,2,3', // 1=Breakfast, 2=Lunch, 3=Dinner
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), null, 422);
        }

        $type = $request->input('type');

        $userWorkoutPlan = UserWorkoutPlan::where('user_id', $user->id)->first();

        if (!$userWorkoutPlan) {
            return $this->sendError('No workout plan found for the user.', null, 404);
        }

        $dietPreferenceIds = QuestionAnswerUser::where('user_id', $user->id)
            ->where('question_id', 9)
            ->pluck('option_id')
            ->toArray();


        $optionToDietPreferenceMap = [
            30 => 1, // Vegan
            28 => 2, // Veg
            29 => 3, // Non-Veg
            31 => 4, // Keto

        ];
        $dietPreferenceIds = array_map(function ($optionId) use ($optionToDietPreferenceMap) {
            return $optionToDietPreferenceMap[$optionId];
        }, $dietPreferenceIds);

        if (empty($dietPreferenceIds)) {
            return $this->sendError('No diet preferences selected by the user.', null, 404);
        }
        //  pree($dietPreferenceIds);
         if (in_array(3, $dietPreferenceIds)) {
            // pree('Non-Veg');
       $meals = Admin_Meal_Entries::with('meal')
            ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
            ->whereHas('meal', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->where('diet_preference', 5) // Select diet_preference field of Admin_Meal_Entries table for Non-Veg
            ->get();
            // pree($meals);
    }
     elseif (in_array(2, $dietPreferenceIds)) {
            // pree('Non-Veg');
       $meals = Admin_Meal_Entries::with('meal')
            ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
            ->whereHas('meal', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->where('diet_preference', 2) // Select diet_preference field of Admin_Meal_Entries table for Non-Veg
            ->get();
            // pree($meals);
    }
      else{  $meals = Admin_Meal_Entries::with('meal')
      
            ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
            ->whereHas('meal', function ($query) use ($type, $dietPreferenceIds) {
                $query->where('type', $type)
                    ->whereIn('diet_preference', $dietPreferenceIds);
            })
            ->get()
            ->unique('meal_id') 
           ->values();
        }
        // pree($dietPreferenceIds);
        // pree($userWorkoutPlan->workout_program_id);
        // pree($type);
        // pree($dietPreferenceIds);
        //  pree($meals);
        if ($meals->isEmpty()) {
            return $this->sendError('No meals found for this type and diet preference.', null, 404);
        }
        $formattedMeals = $meals->map(function ($entry) {
            return [
                'meal_id' => $entry->meal->id,
                'title' => $entry->meal->title,
                'image' => $entry->meal->image ? url($entry->meal->image) : null,
            ];
        });

        return $this->sendResponse([
            'meals' => $formattedMeals,
        ], 'Meals fetched successfully.', 200);
    }


    public function getMealTypesByDietPreference()
    {
        $user = Auth::user();

        // if (!$user) {
        //     return $this->sendError(
        //         'Unauthorized',
        //         null
        //     );
        // }

        // Get the user's workout plan
        $userWorkoutPlan = UserWorkoutPlan::where('user_id', $user->id)->first();

        if (!$userWorkoutPlan) {
            return $this->sendError(
                'No workout plan found for the user.',
                null
            );
        }
        $dietPreferenceIds = QuestionAnswerUser::where('user_id', $user->id)
            ->where('question_id', 9)
            ->pluck('option_id')
            ->toArray();


        $optionToDietPreferenceMap = [
            30 => 1, // Vegan
            28 => 2, // Veg
            29 => 3, // Non-Veg
            31 => 4, // Keto

        ];

        $dietPreferenceIds = array_map(function ($optionId) use ($optionToDietPreferenceMap) {
            return $optionToDietPreferenceMap[$optionId];
        }, $dietPreferenceIds);

        if (empty($dietPreferenceIds)) {
            return $this->sendError('No diet preferences selected by the user.', null, 404);
        }
        // Get meal entries with given diet preference and workout program
        // $mealEntries = Admin_Meal_Entries::with('meal')
        //     ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
        //     ->whereHas('meal', function ($query) {
        //         $query->where('diet_preference', $dietPreferenceIds);
        //     })
        //     ->get();

        $mealEntries = Admin_Meal_Entries::with('meal')
            ->where('workout_program_id', $userWorkoutPlan->workout_program_id)
            ->whereHas('meal', function ($query) use ($dietPreferenceIds) {
                // $query->whereIn('diet_preference', $dietPreferenceIds);
            })
            ->get();

        // Get list of type labels
        $typeLabels = typeLabels();

        // Extract unique types and map them to names
        $mealTypes = $mealEntries->map(function ($entry) {
            return $entry->meal ? $entry->meal->type : null;
        })
            ->filter()
            ->unique()
            ->map(function ($type) use ($typeLabels) {
                return [
                    'type' => $type,
                    'name' => isset($typeLabels[$type]) ? $typeLabels[$type] : 'Unknown',
                ];
            })
            ->values();

        return $this->sendResponse([
            'meal_types' => $mealTypes,
        ], 'Meal types fetched successfully.', 200);
    }
}
