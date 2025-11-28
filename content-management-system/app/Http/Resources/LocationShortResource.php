<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class LocationShortResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        $data =  [];

        if ($this->parks_count) {
            $data["parks_count"] = $this->parks_count;
        }

        if ($this->city) {
            $data["city"] = $this->city;
            $data["city_slug"] = Str::slug($this->city);
        }

        if ($this->state) {
            $data["state"] = $this->state;
            $data["state_slug"] = Str::slug($this->state);
        }

        if ($this->country) {
            $data["country"] = $this->country;
        }

        if ($this->country_short_name) {
            $data["country_short_name"] = $this->country_short_name;
        }

        return $data;
    }
}
