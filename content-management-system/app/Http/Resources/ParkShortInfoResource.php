<?php

namespace App\Http\Resources;

use App\Http\Resources\api\UserBookmarkTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkShortInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $banner = $this->park_images()->where('set_as_banner', "1")->first();
        $user = $request->user();
        $total_ratings = $this->ratings()->where('is_verified', 1)->count();


        // $bookmark = false;
        // if ($user) {
        //     $b = $this->bookmark()->where('user_id', $user->id)->first();
        //     $bookmark = $b ? true : false;
        // }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            "image" => $banner ? new MediaResource($banner->media) : null,
            "address" => $this->address,
            "city" => $this->city,
            "city_slug" => $this->city_slug,
            "state" => $this->state,
            "state_slug" => $this->state_slug,
            "country" => $this->country,
            'country_short_name' => $this->country_short_name,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "bookmark" => (auth()->check()) ? UserParkBookmarkResource::collection($this->bookmark()->where('user_id', $user->id)->get()) : [],
            "avg_ratings" => (float) number_format($this->ratings()->where('is_verified', 1)->avg('rating'), 1),
            "total_ratings" => $total_ratings ?? 0,
            'distance' => $this->distance ?? null,
            'miles' => $this->distance ? $this->distance * 0.62137 : null,
        ];
    }
}
