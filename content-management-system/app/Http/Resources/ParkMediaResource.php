<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ParkMediaResource extends JsonResource
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
            "id" => $this->media->id,
            "name" => $this->media->name,
            "url" => Storage::url($this->media->path),
            "size" => $this->media->size,
            "type" => $this->media->mime_type,
            "thumbnails" => $this->media->get_thumbnails(),
        ];
    }
}
