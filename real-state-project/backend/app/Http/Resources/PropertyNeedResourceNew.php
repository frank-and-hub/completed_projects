<?php

namespace App\Http\Resources;

use App\Models\City;
use App\Models\Country;
use App\Models\Province;
use App\Models\Suburb;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\Property as PropertyHelper;

class PropertyNeedResourceNew extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $additionalFeatures = json_decode($this->additional_features, true);
        $data = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'country' => [
                'country' => $this->country,
                'id' => Country::where('name', $this->country)->first()->id
            ],
            'province' => [
                'province_name' => $this->province_name,
                'id' => Province::where('province_name', $this->province_name)->first()->id
            ],
            'city' => [
                'city' => $this->city,
                'id' => City::where('city_name', $this->city)->first()->id
            ],
            'suburb' => [
                'suburb_name' => $this->suburb_name,
                'id' => Suburb::where('suburb_name', $this->suburb_name)->first()->id
            ],
            'currency' => [
                'currency_name' => $this->currency_name,
                'currency_symbol' => $this->currency_symbol,
            ],
            'property_type' => $this->property_type,
            'start_price' => numberFormat($this->start_price),
            'end_price' => numberFormat($this->end_price),
            'no_of_bedroom' => $this->no_of_bedroom,
            'no_of_bathroom' => $this->no_of_bathroom,
            'pet_friendly' => $additionalFeatures['pet_friendly'] ?? null,
            'parking' => $additionalFeatures['parking'] ?? null,
            'pool' => $additionalFeatures['pool'] ?? null,
            'fully_furnished' => $additionalFeatures['fully_furnished'] ?? null,
            'garage' => $additionalFeatures['garage'] ?? null,
            'garden' => $additionalFeatures['garden'] ?? null,
            'move_in_date' => $additionalFeatures['move_in_date'] ?? null,
            'property_count' => $this->property_count,
        ];

        $propertyHelper = PropertyHelper::featureColumnsByCategory();
        foreach ($propertyHelper as &$category) {
            foreach ($category as $key => &$subCategory) {
                $subCategory = $this->{$key};
            }
            $category = (array_filter($category, fn($value) => $value !== null));
        }
        // Merge helper data into main array
        $data['advanced_feature'] = $propertyHelper;
        return $data;
    }
}
