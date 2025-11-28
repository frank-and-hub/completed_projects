<?php

namespace App\Http\Controllers\api\v1;

use App\Models\BodyType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Exercise;
use Illuminate\Support\Facades\Auth;

class ExerciseController extends BaseApiController
{
    public function getBodyTypes(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError(
                'Unauthorized',
                null
            );
        }

        $bodyTypes = BodyType::all();
        if ($bodyTypes->isEmpty()) {
            return $this->sendError(
                'No body types found.',
                null
            );
        }
        $bodyTypesData = $bodyTypes->map(function ($bodyType) {
            return [
                'id' => $bodyType->id,
                'name' => $bodyType->name,
                'image' => $bodyType->image ? url($bodyType->image) : null,
            ];
        });
        return $this->sendResponse(
            $bodyTypesData,
            'Body types retrieved successfully.'
        );
    }
    public function getExercisesByBodyType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body_type_id' => 'required|exists:ft_ms_body_types,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Validation Error',
                $validator->errors()
            );
        }

        $bodyTypeId = $request->input('body_type_id');

        $exercises = Exercise::where('body_type_id', $bodyTypeId)->get();

        if ($exercises->isEmpty()) {
            return $this->sendError(
                'No exercises found for this body type.',
                null
            );
        }

        $groupedExercises = $exercises->groupBy('location')->mapWithKeys(function ($group, $key) {
            $locationLabel = $key == 1 ? 'Home' : ($key == 2 ? 'Gym' : 'Unknown');

            return [
                $locationLabel => $group->map(function ($exercise) {
                    return [
                        'id' => $exercise->id,
                        'exercise_name' => $exercise->exercise_name,
                        'image' => $exercise->image ? url($exercise->image) : null,
                    ];
                })->values()
            ];
        });

        return $this->sendResponse(
            $groupedExercises,
            'Exercises grouped by location retrieved successfully.'
        );
    }


    public function getExerciseDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exercise_id' => 'required|exists:ft_ms_exercises,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError(
                'Validation Error',
                $validator->errors()
            );
        }

        $exerciseId = $request->input('exercise_id');
        $exercise = Exercise::where('id', $exerciseId)
            ->first();

        if (!$exercise) {
            return $this->sendError(
                'Exercise not found for the specified location.',
                null
            );
        }
        $relatedByBodyType = Exercise::where('body_type_id', $exercise->body_type_id)
            ->where('id', '!=', $exerciseId)
            ->where('location', $exercise->location)
            ->get();

        $relatedByMuscles = $exercise->muscles()->get()
            ->flatMap(function ($muscle) use ($exercise, $exerciseId) {
                return Exercise::whereHas('muscles', function ($query) use ($muscle) {
                    $query->where('ft_ms_muscle_trained.id', $muscle->id);
                })
                    ->where('id', '!=', $exerciseId)
                    ->where('location', $exercise->location)
                    ->get();
            });
            // $relatedbylocation = Exercise::where('location', $exercise->location)
            // ->where('id', '!=', $exerciseId)
            // ->get();

        $combinedRelatedExercises = $relatedByBodyType
            ->merge($relatedByMuscles)
            // ->merge($relatedbylocation)
            ->unique('id')
            ->map(function ($relatedExercise) {
                return [
                    'id' => $relatedExercise->id,
                    'exercise_name' => $relatedExercise->exercise_name,
                    'image' => $relatedExercise->image ? url($relatedExercise->image) : null,
                    'location' => $relatedExercise->location,
                ];
            })
            ->values(); // Reset keys

        return $this->sendResponse(
            [
                'id' => $exercise->id,
                'exercise_name' => $exercise->exercise_name,
                'level' => $exercise->level,
                'equipment' => $exercise->equipment,
                'image' => $exercise->image ? url($exercise->image) : null,
                'video' => $exercise->video ? url($exercise->video) : null,
                'description' => $exercise->description,
                'muscles_trained' => $exercise->muscles()->get()->map(function ($muscle) {
                    return [
                        'id' => $muscle->id,
                        'name' => $muscle->name,
                    ];
                }),
                'More Related Exercises' =>
                $combinedRelatedExercises,
            ],
            'Exercise details retrieved successfully.'
        );
    }
}
