<?php

namespace App\Http\Resources;

use App\Http\Resources\MetaData\ParkShortDataResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ContainerShortInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        $feature = !empty($this->feature()->first()) ? $this->feature()->first() : null;

        $data  = [
            "id" => $this->id,
            "name" => $this->name,
            "title" => $this->title,
            "description" => $this->description,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "parks" => $this->parks ? ParkShortDataResource::collection($this->parks) : null,
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
