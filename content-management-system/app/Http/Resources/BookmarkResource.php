<?php

namespace App\Http\Resources;

use App\Http\Resources\api\UserBookmarkTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class BookmarkResource extends JsonResource
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
            'list' => new UserBookmarkTypeResource($this->bookmarkType),
            'parks' => new ParkShortInfoResource($this->park()->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
                'city',
                'city_slug',
                'state',
                'state_slug',
                'country',
                'country_short_name'
            ])->first())
        ];
    }
}
