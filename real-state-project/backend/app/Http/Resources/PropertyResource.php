<?php

namespace App\Http\Resources;

use App\Models\PropertyClientOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $auth = Auth::check();
        if ($this->showOnMap) {
            $address = $this->streetNumber . ', ' . $this->streetName . ', ' . $this->suburb . ', ' . $this->town . ', ' . $this->province . ', ' . $this->country;
        } else {
            $address = $this->suburb . ', ' . $this->town . ', ' . $this->province . ', ' . $this->country;
        }
        $clientOffice = PropertyClientOffice::where('id', $this->client_office_id)->first();
        return [
            'id' => $this->id,
            'client_office_id' => $this->client_office_id,
            'price' => numberFormat($this->price),
            'currency' => $this->currency,
            'currency_symbol' => $this->currency === 'ZAR' ? 'R' : getCurrencySymbol($this->currency),
            'propertyType' => $this->propertyType,
            'propertyStatus' => $this->propertyStatus,
            'address' => $auth ? $address : '',
            'beds' => $this->beds,
            'baths' => $this->baths,
            'pool' => $this->pool,
            'garages' => $this->garages,
            'petsAllowed' => $this->petsAllowed,
            'propertyFeatures' => $this->propertyFeatures,
            'title' => $this->title,
            'openparking' => $this->openparking,
            'furnished' => $this->furnished,
            'buildingSize' => $this->buildingSize,
            'buildingSizeType' => $this->buildingSizeType,
            'landSize' => $this->landSize,
            'landsizeType' => $this->landsizeType,
            'description' => $this->description,
            'other_features' => [
                'livingAreas' => $this->livingAreas,
                'staffAccommodation' => $this->staffAccommodation,
                'study' => $this->study,
                'carports' => $this->carports,
                'propertyFeatures' => $this->propertyFeatures,
            ],
            'photos' => PropertyImageResource::collection($this->photos),
            'contacts' => PropertyContactResource::collection($this->contacts),
            'client' => $auth ? new PropertyClientOfficeResource($clientOffice) : '',
            'event' => [
                'date' => '',
                'time' => '',
            ]
        ];
    }
}
