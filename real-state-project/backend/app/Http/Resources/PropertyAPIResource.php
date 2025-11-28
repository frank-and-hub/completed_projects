<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Helpers\Property as PropertyHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyAPIResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $address = ($this->address);
        // $address = "{$this->suburb}, {$this->town}, {$this->province}, {$this->country}";
        $data = [
            'clientPropertyID' => $this->id,
            'price' => $this->financials['price'] ? number_format($this->financials['price']) : null,
            'currency' => $this->financials['currency'] ?? null,
            'currency_symbol' => $this->financials['currency_symbol'] ?? null,

            'ratesAndTaxes' => $this->financials['ratesAndTaxes'],
            'levy' => $this->financials['levy'],
            'depositRequired' => $this->financials['depositRequired'],
            'leasePeriod' => $this->financials['leasePeriod'],
            'isReduced' => $this->financials['isReduced'] ? 1 : 0,

            'title' => $this->title,
            'streetNumber' => $address['streetNumber'],
            'streetName' => $address['streetName'],
            'unitNumber' => $address['unitNumber'] ?? null,
            'complexName' => $address['complexName'] ?? null,
            'suburb' => $this->suburb,
            'city' => $this->town,
            'state' => $this->province,
            'country' => $this->country,
            'landSize' => $this->landSize,
            'landSize_unit' => "m2",
            'buildingSize' => $this->buildingSize,
            'buildingSize_unit' => 'meter',
            'propertyType' => $this->propertyType,
            'propertyStatus' => $this->propertyStatus,
            'bedroom' => $this->bedrooms,
            'bathroom' => $this->bathrooms,
            'photos' => InternalPropertyMedia::collection($this->images),
            'description' => $this->description,
            'latitude' => $this->lat,
            'longitude' => $this->lng,
        ];

        $propertyHelper = PropertyHelper::featureColumnsByCategory();

        foreach ($propertyHelper as &$category) {
            foreach ($category as $key => &$subCategory) {
                $subCategory = $this->{$key};
            }
            $category = (array_filter($category, fn($value) => $value !== null));
        }
        $data['advanced_feature'] = $propertyHelper;

        $admin = $this->admin;
        $data['property_agent_details'] = [
            'fullName' => $admin->name ?? null,
            'email' => $admin->email ?? null,
            'phone' => $admin->dial_code . '' . $admin->phone ?? null,
            'image' => $admin?->image?->path ? Storage::url($admin?->image?->path) : null,
        ];

        return $data;
    }
}
