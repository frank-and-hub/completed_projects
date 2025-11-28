<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThankYouResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $property = $this->property;

        $admin = $property->admin;
        $agency = $admin?->agent_agency?->agencyRegister;

        $data['id'] = $this->id;
        $data['pvr_date'] = $this->event_datetime;
        $data['property'] = [
            'id' => $property?->id,
            'name' => $property?->title,
            'address' => "{$property->suburb}, {$property->town}, {$property->province}, {$property->country}",
            'type' => $property?->propertyType,
        ];
        $data['property_user_name'] = $admin->name ? ucwords($admin->name) : null;

        $data['client'] = [
            'name' => $agency?->business_name ? ucwords($agency?->business_name) : null,
            'logo' => $agency?->agency_banner ?? null
        ];

        return $data;
    }
}
