<?php

namespace App\Http\Resources\admin;
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
        return [
            'day'=>$this->day,
            'type'=>$this->type,
            'opening_time'=>$this->opening_time,
            'closing_time'=>$this->closing_time,
            'availability'=>ucwords(Str::of($this->type)->replace("_"," "))
        ];
    }
}
