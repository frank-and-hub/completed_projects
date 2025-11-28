<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutLogSetResource extends JsonResource
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
        $data = [];
        $weights = json_decode($this->weight, true);
        if (is_array($weights)) {
            foreach ($weights as $weight) {
                $data[] = [
                    'weight' => $weight,
                    'unit' => $this->exercise && $this->exercise->id == get_running_id()
                        ? ($setStringArray[$this->set_number] ?? 'kg')
                        : 'kg',
                ];
            }
        }
        return $data;
    }
}
