<?php

namespace App\Http\Resources\api;

use App\Http\Resources\UserBookmarkTypeCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBookmarkTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id'=>$this->id,
            'type'=>ucfirst($this->type)
        ];
    }
}
