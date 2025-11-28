<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\AdminWorkoutSettings;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\QuestionAnswerUser;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Models\Workout_Programs;
use App\Models\UserWorkoutPlan;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProgress;
use App\Models\Workout_Week_Days;
use App\Models\QuestionsOption;
use App\Models\UserExerciseProgressWorkout;
use App\Models\WorkoutExerciseProgressUser;

class QuestionController extends BaseApiController
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Check authentication first
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $questions = Question::whereNull('deleted_at')
            ->orderBy('question_order_for_app')
            ->with(['options' => function ($query) {
                $query->select('id', 'question_id', 'label_for_app', 'value', 'image');
            }])
            ->get(['id', 'title_for_app', 'sub_title_for_app', 'type_for_app']);

        $formatted = $questions->map(function ($question) {
            return [
                'question_id' => $question->id,
                'title'       => $question->title_for_app,
                'subtitle'    => $question->sub_title_for_app,
                'selection_type' => match ($question->type_for_app) {
                    1 => 'single_choice',
                    2 => 'multiple_choice',
                    3 => 'text',
                    4 => 'slider',
                    5 => 'info',
                    default => 'unknown',
                },
                'options' => $question->options
                    ->map(function ($option) {
                        if ($option->id == 71) {
                            return null; // Exclude this one
                        }

                        return [
                            'option_id'        => $option->id,
                            'label'            => $option->label_for_app,
                            'value'            => $option->value,
                            'image'            => $option->image ? asset($option->image) : null,
                            'sublevel_option'  => $option->sublevel_for_app,
                        ];
                    })
                    ->filter()
                    ->values(),

            ];
        });

        return $this->sendResponse($formatted, 'Questions fetched successfully.');
    }

    public function oldstore(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:ft_questions,id',
            'answers.*.option_id' => 'nullable|array', // Allow an array of options
            'answers.*.option_id.*' => 'nullable|exists:ft_question_options,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        foreach ($request->answers as $answerData) {
            foreach ($answerData['option_id'] as $optionId) {
                $answer = QuestionAnswerUser::where('user_id', $user->id)
                    ->where('question_id', $answerData['question_id'])
                    ->where('option_id', $optionId)
                    ->first();

                if (!$answer) {
                    $answer = new QuestionAnswerUser();
                    $answer->user_id = $user->id;
                    $answer->question_id = $answerData['question_id'];
                    $answer->option_id = $optionId;  // Save the option_id
                    $answer->answer = $answerData['answer'] ?? null;
                } else {
                    $answer->answer = $answerData['answer'] ?? $answer->answer;
                }

                $answer->save();
            }
        }

        return $this->sendResponse([
            'user' => new UserResource($user),
        ], 'Answers saved successfully.');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:ft_questions,id',
            'answers.*.option_id' => 'nullable',
            'answers.*.answer' => 'nullable|string',
        ]);

        // Delete existing answers for these questions to avoid duplicates
        $questionIds = collect($request->answers)->pluck('question_id')->unique();
        QuestionAnswerUser::where('user_id', $user->id)
            ->whereIn('question_id', $questionIds)
            ->delete();

        // Save all answers
        foreach ($request->answers as $answerData) {
            $question = Question::find($answerData['question_id']);

            // Handle different question types
            switch ($question->type_for_app) {
                case 1: // single_choice
                    if (isset($answerData['option_id']) && is_array($answerData['option_id'])) {
                        foreach ($answerData['option_id'] as $key => $optionId) {
                            QuestionAnswerUser::create([
                                'user_id' => $user->id,
                                'question_id' => $answerData['question_id'],
                                'option_id' => $optionId,
                                'answer' => null
                            ]);
                        }
                    }
                    break;
                case 4: // slider
                    // if (isset($answerData['option_id']) && !is_array($answerData['option_id'])) {
                    QuestionAnswerUser::create([
                        'user_id' => $user->id,
                        'question_id' => $answerData['question_id'],
                        'option_id' => null,
                        'answer' => $answerData['answer']
                    ]);
                    // }
                    break;

                case 2: // multiple_choice
                    if (isset($answerData['option_id'])) {
                        // Ensure option_id is an array
                        $optionIds = is_array($answerData['option_id']) ?
                            $answerData['option_id'] :
                            [$answerData['option_id']];

                        foreach ($optionIds as $optionId) {
                            QuestionAnswerUser::create([
                                'user_id' => $user->id,
                                'question_id' => $answerData['question_id'],
                                'option_id' => $optionId,
                                'answer' => null
                            ]);
                        }
                    }
                    break;

                case 3: // text
                    QuestionAnswerUser::create([
                        'user_id' => $user->id,
                        'question_id' => $answerData['question_id'],
                        'option_id' => null,
                        'answer' => $answerData['answer'] ?? null
                    ]);
                    break;

                case 5: // info (no answer needed)
                    break;
            }
        }

        // Mark profile as completed
        //$user->update(['is_profile_completed' => 1]);
        $user->is_profile_completed = 1;
        $user->save();

        // Automatically assign workout plan
        $planResponse = $this->assignPersonalizedPlan($user);

        if (!$planResponse['success']) {
            return $this->sendError($planResponse['message'], $planResponse['data'], $planResponse['code']);
        }

        return $this->sendResponse([
            'user' => new UserResource($user),
            'workout_plan' => $planResponse['data'],
            'progress' => $planResponse['progress'],
        ], 'Questionnaire submitted and workout plan assigned');
    }


    public function assignPersonalizedPlan(User $user)
    {
        // $user = $request->user();

        // Step 1: Load user's answers
        $userAnswers = \App\Models\QuestionAnswerUser::where('user_id', $user->id)->get();

        $userOptionMap = [];  // question_id => [option_ids]
        $userAnswerMap = [];  // question_id => answer (text/number)

        foreach ($userAnswers as $ua) {
            // Store text/number answers
            if (!is_null($ua->answer)) {
                $userAnswerMap[$ua->question_id] = trim($ua->answer);
            }

            // Store selected options
            if (!is_null($ua->option_id)) {
                // Special logic for location (question_id = 15)
                if ($ua->question_id == 15) {
                    $option = \App\Models\QuestionsOption::find($ua->option_id);

                    if ($option && isset($option->value)) {
                        $value = strtolower(trim($option->value));

                        // Always include the selected option (home or gym)
                        $userOptionMap[15][] = $option->id;

                        // Add 'both' option for matching if it exists in admin
                        if (in_array($value, ['home', 'gym'])) {
                            $bothOption = \App\Models\QuestionsOption::where('question_id', 15)
                                ->whereRaw('LOWER(value) = ?', ['both'])
                                ->first();

                            if ($bothOption) {
                                $userOptionMap[15][] = $bothOption->id;
                            }
                        }
                    }
                } else {
                    // Regular option mapping
                    $userOptionMap[$ua->question_id][] = $ua->option_id;
                }
            }
        }



        // Step 2: Iterate plans and score each
        $plans = \App\Models\Workout_Programs::where('status', 1)->get();
        $bestScore = -1;
        $bestPlan = null;
        $debugLog = [];

        foreach ($plans as $plan) {
            $conditions = \App\Models\AdminWorkoutSettings::where('workout_program_id', $plan->id)->get();
            $matched = 0;
            $matchBreakdown = [];

            foreach ($conditions as $cond) {
                $qId = $cond->question_id;
                $userOptionIds = $userOptionMap[$qId] ?? [];
                $userAnswer = $userAnswerMap[$qId] ?? null;
                $questionLabel = "Q{$qId}";

                // Case 1: option_id match
                if (!is_null($cond->option_id)) {
                    if (!empty($userOptionIds)) {
                        if (in_array($cond->option_id, $userOptionIds)) {
                            $matched++;
                            $matchBreakdown[] = "{$questionLabel} matched via option_id ({$cond->option_id})";
                            continue;
                        } else {
                            // $matchBreakdown[] = "{$questionLabel} user options [" . implode(',', $userOptionIds) . "] do not contain admin option_id {$cond->option_id}";
                        }
                    } else {
                        $matchBreakdown[] = "{$questionLabel} — user selected no options";
                    }
                }

                // Case 2: exact answer match
                if (!is_null($cond->answer) && $userAnswer !== null && $cond->answer == $userAnswer) {
                    $matched++;
                    $matchBreakdown[] = "{$questionLabel} matched via exact answer [{$userAnswer}]";
                    continue;
                }

                // Case 3: range match using option_id
                if ($cond->option_id && $userAnswer !== null && is_numeric($userAnswer)) {
                    $option = \App\Models\QuestionsOption::find($cond->option_id);

                    if ($option && $option->min_val !== null && $option->max_val !== null) {
                        $userVal = floatval($userAnswer);
                        $min = floatval($option->min_val);
                        $max = floatval($option->max_val);

                        if ($userVal >= $min && $userVal <= $max) {
                            $matched++;
                            $matchBreakdown[] = "{$questionLabel} matched via range [{$min}-{$max}] with value {$userVal}";
                            continue;
                        } else {
                            $matchBreakdown[] = "{$questionLabel} failed range match [{$min}-{$max}] with value {$userVal}";
                        }
                    } else {
                        $matchBreakdown[] = "{$questionLabel} — option range data missing for option_id {$cond->option_id}";
                    }
                }

                // Fallback if no match
                $matchBreakdown[] = "{$questionLabel} failed to match";
            }

            $debugLog[] = [
                'plan_id' => $plan->id,
                'plan_title' => $plan->title ?? 'N/A',
                'matched_score' => $matched,
                'total_conditions' => count($conditions),
                'matched_conditions' => $matchBreakdown,
            ];
            // pree($debugLog);

            // Track best matching plan
            // if ($matched > $bestScore) {
            //     $bestScore = $matched;
            //     $bestPlan = $plan;
            // }
            $locationConditionOptions = $conditions->where('question_id', 15)->pluck('option_id')->toArray();
            $locationUserOptions = $userOptionMap[15] ?? [];
            $locationMatched = empty($locationConditionOptions) || !empty(array_intersect($locationConditionOptions, $locationUserOptions));


            $goalConditionOptions = $conditions->where('question_id', 2)->pluck('option_id')->toArray();
            $goalUserOptions = $userOptionMap[2] ?? [];
            $goalMatched = empty($goalConditionOptions) || !empty(array_intersect($goalConditionOptions, $goalUserOptions));


            if ($locationMatched && $goalMatched && $matched > $bestScore) {
                $bestScore = $matched;
                $bestPlan = $plan;
            }
        }

        if (!$bestPlan) {
            return [
                'success' => false,
                'message' => 'No suitable workout program found',
                'data' => null,
                'code' => 404
            ];
        }


        // pree([
        //     'best_plan' => $bestPlan ? $bestPlan->title : null,
        //     'score' => $bestScore,
        //     'debug' => $debugLog,
        // ]);


        $previousWorkoutPlan = UserWorkoutPlan::where('user_id', $user->id)->first();

        if ($previousWorkoutPlan) {
            // if ($previousWorkoutPlan->workout_program_id != $bestPlan->id) {
                // delete previous progress   
                UserProgress::where('user_id', $user->id)
                    ->where('workout_program_id', $previousWorkoutPlan->workout_program_id)
                    // ->delete();
                    //UPDATE status to 0 and save
                    ->update(['status' => 0]);
                    //also from workout exercise progress user
                UserExerciseProgressWorkout::where('user_id', $user->id)
                    ->whereIn('progress_id', UserProgress::where('user_id', $user->id)
                        ->where('workout_program_id', $previousWorkoutPlan->workout_program_id)
                        ->pluck('id')
                    )
                    ->delete();
                    
            }
        // }


        // Create the plan
        $plan = UserWorkoutPlan::updateOrCreate(
            ['user_id' => $user->id, 'is_active' => true],
            [
                'workout_program_id' => $bestPlan->id,
                'start_date' => now(),
                'end_date' => null,
                'duration_weeks' => $bestPlan->duration_weeks,
                'created_by' => null // Auto-assigned
            ]
        );



        // Create new progress record
        /* $progress = UserProgress::create([
            'user_id' => $user->id,
            'week_id' => Workout_Week_Days::where('workout_program_id', $bestPlan->id)
                ->where('week', 1)
                ->first()->week,

            'day_id' => Workout_Week_Days::where('workout_program_id', $bestPlan->id)
                ->where('week', 1)
                ->where('day_number', 1)
                ->first()->day_number,
            'workout_program_id' => $bestPlan->id,
            'status' => true,
            'is_active' => true
        ]); */

        $weekDays = Workout_Week_Days::where('workout_program_id', $bestPlan->id)->get();

        foreach ($weekDays as $day) {
            UserProgress::create([
                'user_id' => $user->id,
                'week_id' => $day->week,
                'exercise_id' => $day->exercise_id,
                'day_id' => $day->day_number,
                'workout_program_id' => $bestPlan->id,
                'status' => true,
                'is_active' => true,
            ]);
        }

        // Get progress with relationships
        $progress = UserProgress::with(['user', 'workoutProgram'])->find(1);

        return [
            'success' => true,
            'data' => $plan,
            'progress' => $progress,
            'message' => 'Personalized plan assigned'
        ];
    }






    // protected function assignPersonalizedPlan(User $user)
    // {
    //     try {
    //         // Get all answers with their questions and options
    //         $answers = QuestionAnswerUser::with(['question', 'option'])
    //             ->where('user_id', $user->id)
    //             ->get()
    //             ->groupBy('question_id');

    //         // Extract needed values
    //         $goalAnswer = optional($answers->get(2))->first();
    //         $fitnessAnswer = optional($answers->get(5))->first();
    //         $locationAnswer = optional($answers->get(15))->first();


    //         // Get values based on question type
    //         $goal = $goalAnswer ? ($goalAnswer->question->type_for_app == 3 ?
    //             $goalAnswer->answer : ($goalAnswer->option ? $goalAnswer->option->value : null)) : null;

    //         $location = $locationAnswer ? ($locationAnswer->question->type_for_app == 3 ?
    //             $locationAnswer->answer : ($locationAnswer->option ? $locationAnswer->option->value : null)) : 'home';

    //         $level = $fitnessAnswer ? ($fitnessAnswer->question->type_for_app == 3 ?
    //             $fitnessAnswer->answer : ($fitnessAnswer->option ? $fitnessAnswer->option->value : null)) : 'beginner';

    //         // Find matching program with fallbacks
    //         $program = Workout_Programs::query()
    //             ->where('goal', $goal)
    //             ->where('location', $location)
    //             ->when($level, fn($q) => $q->where('level', $level))
    //             ->first();

    //         // Fallback logic
    //         if (!$program) {
    //             $program = Workout_Programs::where('goal', $goal)
    //                 ->where('location', $location)
    //                 ->first();
    //         }

    //         if (!$program) {
    //             $program = Workout_Programs::where('goal', $goal)
    //                 ->orderBy('location')
    //                 ->first();
    //         }

    //         if (!$program) {
    //             $program = Workout_Programs::orderBy('created_at')->first();

    //             if (!$program) {
    //                 throw new \Exception('No workout programs available');
    //             }
    //         }

    //         // Create the plan
    //         $plan = UserWorkoutPlan::updateOrCreate(
    //             ['user_id' => $user->id, 'is_active' => true],
    //             [
    //                 'workout_program_id' => $program->id,
    //                 'start_date' => null,
    //                 'end_date' => null,
    //                 'duration_weeks' => $program->duration_weeks,
    //                 'created_by' => null // Auto-assigned
    //             ]
    //         );

    //         // pree(Workout_Week_Days::where('workout_program_id', $program->id)
    //         //                     ->where('week', 1)
    //         //                     ->first()->week,
    //         //         );

    //         // Create new progress record
    //         $progress = UserProgress::create([
    //             'user_id' => $user->id,
    //             'week_id' => Workout_Week_Days::where('workout_program_id', $program->id)
    //                 ->where('week', 1)
    //                 ->first()->week,

    //             'day_id' => Workout_Week_Days::where('workout_program_id', $program->id)
    //                 ->where('week', 1)
    //                 ->where('day_number', 1)
    //                 ->first()->day_number,
    //             'workout_program_id' => $program->id,
    //             'status' => true,
    //             'is_active' => true
    //         ]);

    //         // Get progress with relationships
    //         $progress = UserProgress::with(['user', 'workoutProgram'])->find(1);

    //         // Mark as completed
    //         // $progress->update(['is_completed' => true]);

    //         return [
    //             'success' => true,
    //             'data' => $plan,
    //             'match_accuracy' => $this->calculateMatchAccuracy($program, $goal, $location, $level),
    //             'progress' => $progress,
    //             'message' => 'Personalized plan assigned'
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'data' => null,
    //             'code' => 400
    //         ];
    //     }
    // }


    protected function calculateMatchAccuracy($program, $goal, $location, $level)
    {
        $score = 0;
        if ($program->goal === $goal) $score += 40;
        if ($program->location === $location) $score += 30;
        if ($program->level === $level) $score += 30;

        return $score;
    }
}
