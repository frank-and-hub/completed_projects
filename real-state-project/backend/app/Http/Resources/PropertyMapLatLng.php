<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyMapLatLng extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'title' => $this->title,
            'address' => $this->suburb . ', ' . $this->town,
            ', ' . $this->province,
            'financials' => ($this->financials) ? [
                'price' => numberFormat($this->financials['price']),
                'currency_symbol' => $this->financials['currency_symbol'] ?? null,
                'currency' => $this->financials['currency'] ?? null
            ] : [
                'price' => numberFormat($this->price),
                'currency_symbol' => $this->currency_symbol ?? null,
                'currency' => $this->currency ?? null
            ],
            'main_image' => $this->media ? new InternalPropertyMedia($this->media->first()) : new PropertyImageResource($this->photos->first()),
            'type' => $this->media ? 'internal' : 'external'
        ];
    }
}
