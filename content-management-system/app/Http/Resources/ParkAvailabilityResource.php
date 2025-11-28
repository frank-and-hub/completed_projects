<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $setting = User::find(1);
        return [
            'day' => $this->day,
            'type' => $this->type,
            // 'opening_time' => $this->opening_time ? Carbon::parse($this->opening_time, $setting->timezone)->setTimezone($request->header('timezone'))->format('H:i:s') : null,
            'opening_time' => $this->opening_time ? Carbon::parse($this->opening_time)->format(config('constants.timeformat')) : null,
            'closing_time' => $this->closing_time ? Carbon::parse($this->closing_time)->format(config('constants.timeformat')) : null,
            // 'closing_time' => $this->closing_time ? Carbon::parse($this->closing_time, $setting->timezone)->setTimezone($request->header('timezone'))->format('H:i:s') : null,

            'availability' => ucwords(Str::of($this->type)->replace("_", " "))
        ];
    }
}
