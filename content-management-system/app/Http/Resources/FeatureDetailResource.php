<?php

namespace App\Http\Resources;

use App\Models\Feature;
use App\Models\ParkFeature;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $parkFeatures = ParkFeature::where('park_id', $this->park_id)->where('feature_type_id', $this->id)->pluck('feature_id')->toArray();
        $features = Feature::whereIn('id', $parkFeatures)->get();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "features" => FeatureResource::collection($features),
            'slug' => $this->slug,
        ];

        if ($this->feature_type) {
            $data['parent_slug'] = $this->feature_type?->slug ?? null;
        }

        if ($this->seo_description) {
            $data['seo_description'] = $this->seo_description;
        }

        return $data;
    }
}
