<?php

namespace App\Http\Resources;

use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CalendarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $property = $this->property ?? null;
        $admin = $this->admin ?? null;

        $data =  [
            'id' => $this->id,
            'date' => $this->event_datetime,
            'time' => Carbon::parse($this->event_datetime, 'Africa/Johannesburg')->format('h:i A'),
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'status' => ($this->status === Calendar::STATUS_PENDING ? Carbon::now('UTC')->isBefore($this->event_datetime) : 1) ? $this->status : 'expired',
        ];

        if ($admin) {
            $data['admin'] = [
                'name' => $admin->name,
                'image' => $admin->image ? Storage::url($admin->image) : null
            ];
        }

        if ($property) {
            $data['property'] = [
                'id' => $property->id,
                'address' => $property->address,
                'complete_address' => $property->propertyAddress(),
                'country' => $property->country,
                'province' => $property->province,
                'town' => $property->town,
                'suburb' => $property->suburb,
            ];

            $data['timeSlot'] = [
                'start_time'  => $property?->propertyTimeSlot?->start_time ?? null,
                'end_time'  => $property?->propertyTimeSlot?->end_time ?? null,
                'days_in_week'  => $property?->propertyTimeSlot?->days_in_week ?? [],
            ];

            $data['time_limit'] = $this->event_datetime->addDays(10);
        }

        return $data;
    }
}
