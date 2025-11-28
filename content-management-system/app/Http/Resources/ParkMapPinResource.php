<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkMapPinResource extends JsonResource
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
            'id' => $this->id,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            'distance' => $this->distance,
            'miles' => $this->distance ? $this->distance*0.62137 : null,
        ];
    }
}
