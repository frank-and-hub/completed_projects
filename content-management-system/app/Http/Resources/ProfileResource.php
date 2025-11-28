<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "username"=>$this->username,
            "email" => $this->email,
            "image" => $this->image ? new MediaResource($this->image) : null,
            "created_at" => $this->created_at->timestamp,
            "is_verified" => $this->email_verified_at ? true : false,
        ];
    }
}
