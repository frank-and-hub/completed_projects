<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProgressResource extends JsonResource
{

    protected $extraData;

    public function __construct($resource, $extraData = [])
    {
        parent::__construct($resource);
        $this->extraData = $extraData;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $data = [
            'id' => $this->id,
            'meso_id' => $this->meso_id,
            'week_id' => $this->week_id,
            'day_id' => $this->day_id,
            'set_id' => $this->set_id,
            'exercise' => new ExerciseResource($this->whenloaded('exercise')),
            'reps' => (string) $this->reps,
            'rpe' => (string) $this->rpe,
            'weight' => json_decode($this->weight),
            'completed_at' => $this->completed_at
        ];
        $data['set'] = new SetResource($this->whenloaded('sets'));
        if(is_array($this->extraData)){
            return array_merge($data, $this->extraData);
        }
        return $data;
    }
}
