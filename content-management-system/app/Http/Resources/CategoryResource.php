<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'slug' => $this->slug,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "description"=>$this->description??null,
            "subcategory" => SubcategoryResource::collection($this->subcategories),
            'meta' => [
                'title' => $this?->meta?->title ?? null,
                'description' => $this?->meta?->description ?? null,
            ]
        ];
    }
}
