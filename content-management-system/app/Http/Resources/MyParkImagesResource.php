<?php

namespace App\Http\Resources;

use App\Models\Media;
use Illuminate\Http\Resources\Json\JsonResource;

class MyParkImagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user_id = $request->user()->id;
        $media_ids = $this->park_images()->where('set_as_banner', '0')->where('status', '1')->where('user_id',$user_id)->limit(5)->pluck('media_id')->toArray();
        $media = Media::whereIn('id', $media_ids)->get();
        return [
            'id' => $this->id,
            'name' => $this->name,
            "address" => $this->address,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "images" => count($media) > 0 ? MediaResource::collection($media) : null,
        ];
    }
}
