<?php

namespace App\Http\Resources\MetaData;

use App\Http\Resources\ProfileShortInfoResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkRatingShortDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $banner = $this->park?->park_images()->where('set_as_banner', "1")->first();
        return [
            "id" => $this->id,
            "user" => new ProfileShortInfoResource($this->user),
            "park" => [
                'id' => $this->park->id,
                'name' => $this->park->name,
                'address' => $this->park->address,
            ],
            "rating" => $this->rating,
            "review" => $this->review,
            "is_verified" => $this->is_verified ? true : false,
            "created_at" => Carbon::parse($this->created_at)->timestamp
        ];
    }
}
