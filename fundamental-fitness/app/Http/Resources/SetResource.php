<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $setStringArray = [
            1 => 'min',
            2 => 'km',
        ];

        $data = [
            'id' => (int) $this->id,
            'rest_unit' => $this->rest_unit,
            'rpe_percentage' => $this->rpe_percentage,
            'rest' => (int) $this->rest,
            'rpe' => (string) $this->rpe,
            'status' => $this->status != 0 ? true : false,
        ];

        if ($this->exercise && $this->exercise->id == get_running_id()) {
            $data += [
                'set_number' => 1,
                'set' => $setStringArray[$this->set_number] ?? null,
                'run' => (string) $this->reps,
                'walk' => (int) $this->rest,
                'type' => 'run',
            ];
        } else {
            $data += [
                'set_number' => (int) $this->set_number,
                'reps' => (string) $this->reps,
                'type' => 'workout',
            ];
        }

        return $data;
    }
}
