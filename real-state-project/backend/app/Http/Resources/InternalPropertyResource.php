<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Property as PropertyHelper;
use Illuminate\Support\Facades\Auth;

class InternalPropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $auth = Auth::check();
        $address = "{$this->suburb}, {$this->town}, {$this->province}, {$this->country}";
        $latest_calendar = $this->latest_calendar ?? null;
        $data = [
            'id' => $this->id,
            'price' => $this->financials['price'] ? numberFormat($this->financials['price']) : null,
            'currency' => $this->financials['currency'] ?? null,
            'currency_symbol' => $this->financials['currency_symbol'] ?? null,
            'title' => $this->title,
            'address' => $auth ? $address : '',
            'landSize' => $this->landSize,
            'landSize_unit' => "m2",
            'buildingSize' => $this->buildingSize,
            'buildingSize_unit' => 'meter',
            'propertyType' => $this->propertyType,
            'propertyStatus' => $this->propertyStatus,
            'beds' => $this->bedrooms,
            'baths' => $this->bathrooms,
            'photos' => InternalPropertyMedia::collection($this->images),
            'description' => $this->description,
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

        $admin = $this->admin;
        $agency = $admin?->agent_agency?->agencyRegister;

        $data['property_handle_details'] = [
            'id' => $admin?->id ?? null,
            'fullName' => $admin->name ? ucwords($admin->name) : null,
            'email' => $auth ? ($admin->email ?? null) : null,
            'phone' => $auth ? ($admin->dial_code . ' ' . $admin->phone ?? null) : null,
            'image' => $admin?->image()?->first()?->path ? Storage::url($admin?->image()->first()?->path) : null,
            'role' => $admin->getRoleNames()->first()
        ];

        $data['client'] = [
            'name' => $agency?->business_name ? ucwords($agency?->business_name) : null,
            'logo' => $agency?->agency_banner ?? null
        ];

        $data['event'] = [
            'date_time' => $this?->latest_event_datetime ?? null,
            'id' => $latest_calendar?->id ?? null,
            'status' => $latest_calendar?->status ?? null,
            'time_limit' => $latest_calendar?->event_datetime
                ? $latest_calendar->event_datetime->addDays(10)
                : null,
        ];

        $data['timeSlot'] = [
            'start_time'  => $this?->propertyTimeSlot?->start_time ?? null,
            'end_time'  => $this?->propertyTimeSlot?->end_time ?? null,
            'days_in_week'  => $this?->propertyTimeSlot?->days_in_week ?? [],
        ];

        return $data;
    }
}
