<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureTypeDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "features" => FeatureResource::collection($this->features()->orderBy('name', 'asc')->get()),
            'slug' => $this->slug,
        ];

        if ($this->seo_description) {
            $data['seo_description'] = $this->seo_description;
        }

        return $data;
    }
}
