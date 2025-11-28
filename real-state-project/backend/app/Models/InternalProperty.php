<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalProperty extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'is_agency',
        'title',
        'financials',
        'landSize',
        'buildingSize',
        'propertyType',
        'propertyStatus',
        'country',
        'province',
        'town',
        'suburb',
        'address',
        'showOnMap',
        'bedrooms',
        'bathrooms',
        'location_views',
        'connectivity',
        'outdoor_areas',
        'parking',
        'security_features',
        'energy_efficiency',
        'furnishing',
        'kitchen_features',
        'cooling_heating',
        'laundry_facilities',
        'technology',
        'pet_policy',
        'leisure_amenities',
        'building_features',
        'flooring',
        'proximity',
        'storage_space',
        'communal_areas',
        'maintenance_services',
        'water_features',
        'entertainment',
        'accessibility',
        'lease_options',
        'location_features',
        'noise_control_features',
        'fire_safety_features',
        'description',
        'lat',
        'lng',
        'coordinate',
        'action',
        'status',
        'availableSubscription_id'
    ];

    protected $casts = [
        'location_views' => 'json',
        'connectivity' => 'json',
        'outdoor_areas' => 'json',
        'parking' => 'json',
        'security_features' => 'json',
        'energy_efficiency' => 'json',
        'furnishing' => 'json',
        'kitchen_features' => 'json',
        'cooling_heating' => 'json',
        'laundry_facilities' => 'json',
        'technology' => 'json',
        'pet_policy' => 'json',
        'leisure_amenities' => 'json',
        'building_features' => 'json',
        'flooring' => 'json',
        'proximity' => 'json',
        'storage_space' => 'json',
        'communal_areas' => 'json',
        'maintenance_services' => 'json',
        'water_features' => 'json',
        'entertainment' => 'json',
        'accessibility' => 'json',
        'lease_options' => 'json',
        'location_features' => 'json',
        'noise_control_features' => 'json',
        'fire_safety_features' => 'json',
        'address' => 'json',
        'financials' => 'json',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function media()
    {
        return $this->hasMany(InternalPropertyMedia::class, 'internal_property_id');
    }

    public function images()
    {
        return $this->media()->where('media_type', 'image');
    }

    public function imageIsMain()
    {
        return $this->media()->where('media_type', 'image')->where('isMain', 1);
    }

    public function sentProperties()
    {
        return $this->hasMany(SentInternalPropertyUser::class, 'internal_property_id');
    }

    public function propertyAddress()
    {
        $address = $this->address ?? [];
        $suburb = $this->suburb ?? '';
        $town = $this->town ?? '';
        $province = $this->province ?? '';
        $country = $this->country ?? '';

        $parts = [];

        if (!empty($address['streetNumber'])) {
            $parts[] = $address['streetNumber'];
        }

        if (!empty($address['streetName'])) {
            $parts[] = $address['streetName'];
        }

        if (!empty($address['unitNumber'])) {
            $parts[] = 'Unit ' . $address['unitNumber'];
        }

        if (!empty($address['complexName'])) {
            $parts[] = $address['complexName'];
        }

        if (!empty($suburb)) {
            $parts[] = $suburb;
        }

        if (!empty($town)) {
            $parts[] = $town;
        }

        if (!empty($province)) {
            $parts[] = $province;
        }

        if (!empty($country)) {
            $parts[] = $country;
        }

        return implode(', ', $parts);
    }

    public function contract()
    {
        return $this->belongsToMany(Contract::class, PropertyContract::class)
            ->where('deleted_at', null);
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class, 'internal_propertie_id', 'id');
    }

    public function getLatestEventDateTimeAttribute()
    {
        return $this->latest_calendar ? $this->latest_calendar?->event_datetime : null;
    }

    public function getLatestCalendarAttribute()
    {
        return Calendar::where('internal_propertie_id', $this->id)
            ->whereUserId(auth()?->user()?->id)
            ->whereStatus(Calendar::STATUS_PENDING)
            ->orderBy('event_datetime', 'desc')
            ->first();
    }

    public function propertyTimeSlot()
    {
        return $this->hasOne(PropertyTimeSlot::class, 'internal_property_id', 'id');
    }

    public function getFullAddressAttribute()
    {
        return $this->propertyAddress();
    }
}
