<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkAdminssionResource extends JsonResource
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
            'is_paid'=>$this->is_paid,
            'ticket_amount'=>$this->ticket_amount,
            'instruction_url'=>$this->instruction_url,
            'instructions'=>$this->instructions,
        ];
    }
}
