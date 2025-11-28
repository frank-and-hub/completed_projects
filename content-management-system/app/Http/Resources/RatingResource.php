<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $park = $this->park ?? null;
        $banner = $park?->park_images()->where('set_as_banner', "1")->first();

        return [
            "id" => $this->id,
            "user" => new ProfileShortInfoResource($this->user),
            "park" => [
                'id' => $park->id,
                'name' => $park->name,
                'slug' => $park->slug,
                'address' => $park->address,
                'images' => $banner ? new MediaResource($banner->media) : null,
                "city" => $park->city,
                "city_slug" => $park->city_slug,
                "state" => $park->state,
                "state_slug" => $park->state_slug,
                "country" => $park->country,
                'country_short_name' => $park->country_short_name,
            ],
            "rating" => $this->rating,
            "review" => $this->review,
            "is_verified" => $this->is_verified ? true : false,
            "created_at" => Carbon::parse($this->created_at)->timestamp
        ];
    }
}
