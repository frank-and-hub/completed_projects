<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkImageResource extends JsonResource
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
            "user" => $this->user ? new UserShortInfoResource($this->user) : null,
            "image" => new MediaResource($this->media),
            "likes" => $this->likes->count(),
            "is_liked" => $this->likes->contains('user_id', auth()->id()),
            "is_verified" => $this->is_verified ? true : false,
            "created_at" => Carbon::parse($this->created_at)->timestamp
        ];
    }
}
