<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $bookmark = null;
        if ($this->custom_type == 'park') {
            $banner = $this->park_images()->where('set_as_banner', "1")->first();
            $user = $request->user();
            $bookmark = false;
            if ($user) {
                $b = $this->bookmark()->where('user_id', $user->id)->first();
                $bookmark = $b ? true : false;
            }
            $total_ratings = $this->ratings()->where('is_verified', 1)->count();
        } else {
            $total_ratings = 0;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            "image" => ($this->custom_type == 'park') ? ($banner ? new MediaResource($banner->media) : null) : ($this->image ? new MediaResource($this->image) : null),
            "address" => $this->address,
            "city" => $this->city,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "bookmark" => (auth()->check() && $this->custom_type == 'park') ? UserParkBookmarkResource::collection($this->bookmark()->where('user_id', $user->id)->get()) : [],
            "avg_ratings" => ( $this->custom_type == 'park') ? ((float) number_format($this->ratings()->where('is_verified', 1)->avg('rating'), 1)) : 0,
            "total_ratings" => $total_ratings,
            "custom_type" => $this->custom_type,
            'distance' => $this->distance ?? null,
            'miles' => $this->distance ? $this->distance * 0.62137 : null,
        ];
    }
}
