<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeekResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $data = [
            'id' => $this?->id,
            'week_number' => $this?->week_number,
        ];
        foreach ($dayNames as $index => $dayName) {
            $data['days'][] = [
                'id'   => $index + 1,
                'name' => $dayName,
                'exercise' => []
            ];
        }
        return $data;
    }
}
