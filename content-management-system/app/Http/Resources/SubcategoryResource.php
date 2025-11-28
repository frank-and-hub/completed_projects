<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $parent = $this->category ?? null;
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => 'child',
            "description" => $this->description,
            "image" => $this->image ? new MediaResource($this->image) : null,
            'slug' => $this->slug,
            'meta' => [
                'title' => $this?->meta?->title ?? null,
                'description' => $this?->meta?->description ?? null,
            ],
        ];

        if ($parent) {
            $data['parent'] = [
                'id' => $parent->id,
                'name' => $parent->name,
                'slug' => $parent->slug,
            ];
        }

        return $data;
    }
}
