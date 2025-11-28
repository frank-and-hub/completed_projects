<?php

namespace App\Http\Resources;

use App\Models\Parks;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ParksByCategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $nearby_parks_query = Parks::where('active', 1)->whereHas('categories', function ($query) {
            $query->where('category_id', $this->id);
        });

        if (!empty($request->header('latitude')) && !empty($request->header('longitude'))) {
            $nearby_parks_query = $nearby_parks_query->select([
                'parks.*',
                DB::raw('ROUND((6371 * acos( cos( radians(' . $request->header('latitude') . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $request->header('longitude') . ') ) + sin( radians(' . $request->header('latitude') . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
            ])->where('parks.active', 1)->orderBy('distance');
        }

        $nearby_parks_query = $nearby_parks_query->limit(5)->get();
        return [
            'id' => $this->id,
            'title' => $this->name,
            'slug' => $this->slug,
            "type" => 'park',
            'description' => $this->description ?? null,
            "data" => ParkShortInfoResource::collection($nearby_parks_query)
        ];
    }
}
