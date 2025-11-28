<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $parent = $this->feature_type ?? null;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'custom_type' => $this->type,
            "image" => $this->image ? new MediaResource($this->image) : null,
            'slug' => $this->slug,
        ];

        if ($this->feature_type) {
            $data['parent_slug'] = $this->feature_type?->slug ?? null;
        }

        if ($this->parks_count) {
            $data['parks_count'] = $this->parks_count ?? 0;
        }

        if ($parent) {
            $data['parent'] = [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
            ];
        }

        if ($this->seo_description) {
            $data['seo_description'] = $this->seo_description;
        }

        return $data;
    }
}
