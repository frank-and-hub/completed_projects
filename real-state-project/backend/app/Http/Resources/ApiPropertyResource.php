<?php

namespace App\Http\Resources;

use App\Helpers\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ApiPropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $checkbox_columns = Property::featureColumnsByCategory();
        // $checkbox_columns_array = [];
        $checkbox_columns = Property::featureColumnsByCategory();
        $checkbox_columns_array = [];
        if (!empty($checkbox_columns)) {
            foreach ($checkbox_columns as $category_key => $checkbox_column_) {
                if (!empty($checkbox_column_)) {
                    // Format the category name (capitalized, human-readable)
                    $formatted_category_name = ucwords(checkBoxTextUpadte($category_key));
                    $checkbox_columns_array[$formatted_category_name] = [];
                    foreach ($checkbox_column_ as $subcategory_key => $checkbox_column) {
                        if (!empty($checkbox_column)) {
                            // Format subcategory name
                            $formatted_subcategory_name = checkBoxTextUpadte($subcategory_key);
                            $checkbox_columns_array[$formatted_category_name][$formatted_subcategory_name] = [];
                            foreach ($checkbox_column as $key => $checkbox_item) {
                                if (!empty($checkbox_item)) {
                                    // Assuming you want to store the formatted value of each checkbox item
                                    $checkbox_columns_array[$formatted_category_name][$formatted_subcategory_name][$key] = checkBoxTextUpadte($checkbox_item);
                                }
                            }
                        }
                    }
                }
            }
        }
        // foreach ($checkbox_columns as $key => $checkbox_column_) {
        //     $checkbox_columns_array[ucwords(checkBoxTextUpadte($key))] = [];
        //     foreach ($checkbox_column_ as $key => $checkbox_column) {
        //         $checkbox_columns_array[ucwords(checkBoxTextUpadte($key))][checkBoxTextUpadte([$key])] = [];
        //         foreach ($checkbox_column as $checkbox_colum) {
        //             $checkbox_columns_array[ucwords(checkBoxTextUpadte($key))][checkBoxTextUpadte([$key])] = checkBoxTextUpadte($checkbox_colum);
        //         }
        //     }
        // }

        $additionalFeatures = json_decode($this->additional_features, true);
        $customerDetails = [
            'name' => $this->user->name,
            'phone' => $this->user->country_code . ' ' . $this->user->phone,
            'email' => $this->user->email,
            'image' => $this->user->image ? Storage::url($this->user->image) : '',
        ];
        $responce = [
            'propertyType' => $this->property_type,
            'province' => $this->province_name,
            'town' => $this->city,
            'suburb' => $this->suburb_name,
            'priceRange' => $this->start_price . ' - ' . $this->end_price,
            'beds' => $this->no_of_bedroom,
            'baths' => $this->no_of_bathroom,
            'customerDetails' => $customerDetails ?: null,
            'propertyFeatures' => $checkbox_columns_array,
        ];
        if ($additionalFeatures) {
            $responce['petFriendly'] = array_key_exists('pet_friendly', $additionalFeatures) ? $additionalFeatures['pet_friendly'] : null;
            $responce['parking'] = array_key_exists('parking', $additionalFeatures) ? $additionalFeatures['parking'] : null;
            $responce['pool'] = array_key_exists('pool', $additionalFeatures) ? $additionalFeatures['pool'] : null;
            $responce['fullyFurnished'] = array_key_exists('fully_furnished', $additionalFeatures) ? $additionalFeatures['fully_furnished'] : null;
            $responce['garage'] = array_key_exists('garage', $additionalFeatures) ? $additionalFeatures['garage'] : null;
            $responce['garden'] = array_key_exists('garden', $additionalFeatures) ? $additionalFeatures['garden'] : null;
            $responce['moveInDate'] = array_key_exists('move_in_date', $additionalFeatures) ? $additionalFeatures['move_in_date'] : '';
        }

        return $responce;
    }
}
