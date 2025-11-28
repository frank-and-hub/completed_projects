<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sets = $this->whenLoaded('sets')->flatMap(function ($set) {
            return WorkoutLogSetResource::collection([$set])->resolve();
        })->values();
        $data = [
            'exercise' => new ExerciseResource($this->whenloaded('exercise')),
            'image' => $this->image ? asset($this->image) : null,
            'workout_sets' => array_merge(...$sets),
        ];
        return $data;
    }
}
