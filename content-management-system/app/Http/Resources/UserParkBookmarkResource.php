<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserParkBookmarkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->bookmarkType->id;
        // return[
        //     'id'=>$this->bookmarkType->id,
        //     'type'=>ucfirst($this->bookmarkType->type)
        // ];
    }
}
