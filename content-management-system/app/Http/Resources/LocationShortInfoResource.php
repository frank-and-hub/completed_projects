<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ContainerShortInfoResource;
use App\Http\Resources\MediaResource;

class LocationShortInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $banner = $this?->banner ?? null;
        $thumbnail = $this?->thumbnail ?? null;
        $containers = $this?->containers ?? null;
        $defaultContainer = $this?->default_container ?? null;

        $data =  [
            "id" => $this?->id ?? null,
            'city' => $this?->city ?? null,
            'city_slug' => $this?->city_slug ?? null,
            'state' => $this?->state ?? null,
            'state_slug' => $this?->state_slug ?? null,
            'country' => $this?->country ?? null,
            'country_short_name' => $this?->country_short_name ?? null,
            'title' => $this?->title ?? null,
            'subtitle' => $this?->subtitle ?? null,
            'default_container' => [
                'id' => $defaultContainer ? (int) $defaultContainer->id : null,
                'fetaure' => [
                    'id' => $defaultContainer ? $defaultContainer?->feature()->first()->id ?? null : null,
                    'name' => $defaultContainer ? $defaultContainer?->feature()->first()?->name ?? null : null,
                    'slug' => $defaultContainer ? $defaultContainer?->feature()->first()?->slug ?? null : null,
                ],
            ],
            'status' => $this?->status ?? null,
            'longitude' => $this?->location_longitude ?? null,
            'latitude' => $this?->location_latitude ?? null,
            'seo_description' => $this->seo_description  ?? null,
        ];

        if ($thumbnail) {
            $data['thumbnail'] =  new MediaResource($thumbnail);
        }

        if ($banner) {
            $data['banner'] =  new MediaResource($banner);
        }

        if ($containers) {
            $data['containers'] = ContainerShortInfoResource::collection($containers);
        }

        return $data;
    }
}
