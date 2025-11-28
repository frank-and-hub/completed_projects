<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $start = Carbon::parse($this->event_datetime, 'Africa/Johannesburg')->addHours(2);
        // $end = Carbon::parse($this->event_datetime, 'Africa/Johannesburg');

        $start = Carbon::parse($this->event_datetime);

        return [
            'title' => $this->description . ' with ' . $this->user?->name,
            'start' => $start,
            'end' => $start,
            'color' => 'F30051',
            'textColor' => 'white',
            'kind' => $this->status == 'pending' ? ($this->isExpiry() ? 'meeting' : 'appointment') : ($this->status == ('accepted' || 'completed') ? 'holiday' : 'concert'),
            'state' => 'sh',
        ];
    }
}
