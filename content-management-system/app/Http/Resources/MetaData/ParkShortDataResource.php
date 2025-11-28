<?php

namespace App\Http\Resources\MetaData;

use App\Http\Resources\MediaResource;
use App\Http\Resources\ParkMediaResource;
use App\Http\Resources\UserParkBookmarkResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkShortDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $banner = $this->park_images()->where('status', '1')->where('set_as_banner', '1')->first();
        $total_ratings = $this->ratings()->where('is_verified', 1)->count();
        $media = $this->park_images()->where('set_as_banner', '0')->where('status', '1')->where(function ($query) {
            $query->where('is_verified', true)->orWhereNull('user_id');
        })->orderBy('sort_index')->limit(5)->get();
        $user = $request->user();
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            "banner_image" => $banner ? new MediaResource($banner->media) : null,
            "images" => count($media) > 0 ? ParkMediaResource::collection($media) : null,
            "address" => $this->address,
            "city" => $this->city,
            'city_slug' => $this->city_slug,
            "state" => $this->state,
            'state_slug' => $this->state_slug,
            "country" => $this->country,
            'country_short_name' => $this->country_short_name,
            "timezone" => $this->timezone,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "description" => $this->description,
            "bookmark" => (auth()->check()) ? UserParkBookmarkResource::collection($this->bookmark()->where('user_id', $user->id)->get()) : [],
            "rating" => [
                "total_ratings" => $total_ratings,
                "5" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 5)->where('is_verified', 1)->count() / $total_ratings * 100, 1)  : 0,
                "4" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 4)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "3" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 3)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "2" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 2)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "1" => $total_ratings ? (float) number_format($this->ratings()->where("rating", 1)->where('is_verified', 1)->count() / $total_ratings * 100, 1) : 0,
                "avg_ratings" => (float) number_format($this->ratings()->where('is_verified', 1)->avg('rating'), 1),
            ],
            "created_at" => Carbon::parse($this->created_at)->timestamp,
            'slug' => $this->slug
        ];
        return $data;
    }
}
