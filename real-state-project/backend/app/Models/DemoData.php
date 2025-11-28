<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoData extends Model
{
    protected $table = 'demo_data';

    public $timestamps = true;

    protected $fillable = [
        'title',
        'financial',
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
        'action',
        'description_json',
        'status',
        'images'
    ];

    public function getFinancialsAttribute()
    {
        return $this->financial ? json_decode($this->financial) : null;
    }

    public function getPhotosAttribute()
    {
        return $this->images ? json_decode($this->images) : null;
    }

    public function getAddresAttribute()
    {
        return $this->address ? json_decode($this->address) : null;
    }
}
