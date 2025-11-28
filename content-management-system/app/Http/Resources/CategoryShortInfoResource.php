<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryShortInfoResource extends JsonResource
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
            'description' => $this->description ?? null,
            'image' => $this->image ? new MediaResource($this->image) : null,
            'meta' => [
                'title' => $this?->meta?->title ?? null,
                'description' => $this?->meta?->description ?? null,
            ]
        ];
    }
}
