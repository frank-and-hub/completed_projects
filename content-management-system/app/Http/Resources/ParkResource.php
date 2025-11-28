<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $banner = $this->park_images()->where('set_as_banner',"1")->first();
        $user = $request->user();
        $bookmark = false;
        if ($user) {
            $b = $this->bookmark()->where('user_id', $user->id)->first();
            $bookmark = $b ? true : false;
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            "image" => $banner ? new MediaResource( $banner->media) : null,
            "bookmark" => $bookmark,
            'slug' => $this->slug,
            "city" => $this->city,
            "city_slug" => $this->city_slug,
            "state" => $this->state,
            "state_slug" => $this->state_slug,
            "country" => $this->country,
            'country_short_name' => $this->country_short_name,
        ];
    }
}

