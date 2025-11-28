<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranscationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'purchase_date' => $this->created_at,
            'transcation_id' => $this->pf_payment_id ?? 'N/A',
            'started_at' => convertToSouthAfricaTime($this->started_at, timeZone: 'Africa/Johannesburg', time: false),
            'expired_at' => convertToSouthAfricaTime($this->expired_at, timeZone: 'Africa/Johannesburg', time: false),
            'plan_type' => $this->plan->plan_name,
            'amount' => $this->amount,
            'no_of_requests' => $this->no_of_request,
            'schedule' => [
                'start_time' => $this?->user_schedule_time?->start_time ??  null,
                'end_time' => $this?->user_schedule_time?->end_time ??  null
            ]
        ];
    }
}
