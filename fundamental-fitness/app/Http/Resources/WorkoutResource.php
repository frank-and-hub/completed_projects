<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class WorkoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $levelArray = [
            1 => 'Beginner',
            2 => 'Intermediate',
            3 => 'Advanced',
        ];

        $userId = Auth::user()->id;

        $data = [
            'id' => $this->id,
            'workout_frequency_id' => (int) $this->workout_frequency->id,
            'meso_id' => (int) $this->meso_id,
            'day_id' => $this->day_id,
            'week_id' => $this->week_id,
            'exercise' => new ExerciseResource($this->whenloaded('exercise')),
            'level' => $levelArray[$this->level],
            'image' => $this->image ? asset($this->image) : null,
            'video' => $this->video ? asset($this->video) : null,
            'gif' => $this->gif ? asset($this->gif) : null,
            'is_completed' => is_exercise_completed($userId, $this->exercise_id, $this->day_id, $this->week_id, $this->meso_id),
            'sets' => SetResource::collection($this->whenloaded('sets')),
        ];

        return $data;
    }
}