<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyClientOfficeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'clientOfficeID' => $this->clientOfficeID,
            'name' => ucwords($this->name),
            'tel' => $this->tel,
            'fax' => $this->fax,
            'email' => $this->email,
            'website' => $this->website,
            'logo' => $this->logo,
            'officereference' => $this->officereference,
            'profile' => $this->profile,
            'physicalAddress' => $this->physicalAddress,
            'country' => 'South Africa',
            // 'timeStamp' => $this->timeStamp,
        ];
    }
}
