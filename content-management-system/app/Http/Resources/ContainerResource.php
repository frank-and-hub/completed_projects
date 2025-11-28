<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $feature = !empty($this->feature()->first()) ? $this->feature()->first() : null;

        $data  = [
            "id" => $this->id,
            "name" => $this->name,
            "title" => $this->title,
            "description" => $this->description,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "parks" => $this->parks ? ParkDetailResource::collection($this->parks) : null,
        ];

        if ($feature) {
            $data['feature'] = [
                'id' => $feature->id,
                'name' => $feature->name,
                'slug' => $feature->slug,
            ];
        }

        return $data;
    }
}
