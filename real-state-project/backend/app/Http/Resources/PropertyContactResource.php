<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PropertyContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $auth = Auth::user();
        return [
            'fullName' => $this->fullName,
            'image' => $this->logo,
            'phone' => $auth ? $this->cell : '',
            'email' => $auth ? $this->email : '',
        ];
    }
}
