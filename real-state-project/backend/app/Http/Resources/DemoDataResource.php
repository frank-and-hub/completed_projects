<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Property as PropertyHelper;

class DemoDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $address = "{$this->suburb}, {$this->town}, {$this->province}, {$this->country}";
        $data = [
            'id' => $this->id,
            'price' => $this->financials->price ? numberFormat($this->financials->price) : null,
            'currency' => $this->financials->currency ?? null,
            'currency_symbol' => $this->financials->currency_symbol ?? null,
            'title' => $this->title,
            'address' => $address,
            'full_address' => json_decode($this->address),
            'country' => $this->country,
            'province' => $this->province,
            'town' => $this->town,
            'suburb' => $this->suburb,
            'landSize' => $this->landSize,
            'landSize_unit' => "m2",
            'buildingSize' => $this->buildingSize,
            'buildingSize_unit' => 'meter',
            'propertyType' => $this->propertyType,
            'propertyStatus' => $this->propertyStatus,
            'beds' => $this->bedrooms,
            'baths' => $this->bathrooms,
            'photos' => $this->photos,
            'description' => $this->description,
        ];

        $propertyHelper = PropertyHelper::featureColumnsByCategory();
        foreach ($propertyHelper as &$category) {
            foreach ($category as $key => &$subCategory) {
                $subCategory = json_decode($this->{$key});
            }
            $category = (array_filter($category, fn($value) => $value !== null));
        }

        $data['advanced_feature'] = $propertyHelper;
        $data['property_handle_details'] = [
            'id' => null,
            'fullName' => null,
            'email' => null,
            'phone' => null,
            'image' => null,
            'role' => null,
        ];

        $data['client'] = [
            'name' => null,
            'logo' => null
        ];

        $data['event'] = [
            'date_time' => $this?->latest_event_datetime ?? null,
        ];

        return $data;
        // return parent::toArray($request);
    }
}
