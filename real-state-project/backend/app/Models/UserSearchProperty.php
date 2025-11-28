<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Property as HelpersProperty;

class UserSearchProperty extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'user_search_property';
    protected $guarded = [];
    // protected $fillable = ['user_id','user_subscription_id','province_name','suburb_name','city','property_type','start_price','end_price','no_of_bedroom','no_of_bathroom','additional_features','send_status'];

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
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user_subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function getProperties2Attribute()
    {
        $property_columns_keys = array_keys(HelpersProperty::featureColumns());

        $query = InternalProperty::where('propertyStatus', '!=', 'Inactive')
            ->whereStatus(1)
            ->where(function ($query) {
                $query
                    ->where('financials->price', '>=', (int) $this->start_price)
                    ->where('financials->price', '<=', (int) $this->end_price);
            });

        // Fix 1: Pass `$this` explicitly using `use`
        $query = $query->where(function ($query) {
            $query = $query
                ->when($this->country, function ($que) {
                    $que->where('country', $this->country);
                })
                ->when($this->province_name, function ($que) {
                    $que->where('province', $this->province_name);
                })
                ->when($this->city, function ($que) {
                    $que->where('town', $this->city);
                })
                ->when($this->suburb_name, function ($que) {
                    $que->where('suburb', $this->suburb_name);
                });
        });

        // Fix 2: Pass `$this` explicitly using `use`
        $query = $query->where(function ($query) {
            $query = $query->where(function ($query) {
                $query->where('propertyType', $this->property_type);
            });
        });

        // Fix 3: Pass `$this` and `$property_columns_keys` explicitly using `use`
        $query = $query->where(function ($query) use ($property_columns_keys) {
            $query->where(function ($query) use ($property_columns_keys) {
                if ($this->no_of_bedroom == 5) {
                    $query->orWhere('bedrooms', '>=', $this->no_of_bedroom);
                } else {
                    $query->where(function ($query) {
                        $query->where('bedrooms', $this->no_of_bedroom)
                            ->orWhereRaw('? BETWEEN FLOOR(bedrooms) AND CEIL(bedrooms)', [$this->no_of_bedroom]);
                    });
                }
                if ($this->no_of_bathroom == 5) {
                    $query->orWhere('bathrooms', '>=', $this->no_of_bathroom);
                } else {
                    $query->where(function ($query) {
                        $query->where('bathrooms', $this->no_of_bathroom)
                            ->orWhereRaw('? BETWEEN FLOOR(bathrooms) AND CEIL(bathrooms)', [$this->no_of_bathroom]);
                    });
                }
                foreach ($property_columns_keys as $property_columns_key) {
                    if ($this->{$property_columns_key}) {
                        $query = $query->orwhereJsonContains($property_columns_key, $this->{$property_columns_key});
                    }
                }
            });
        });
        return $query->get();
    }

    public function getPropertiesAttribute()
    {
        $internal_property = $this->internal_property()->get();
        $external_property = $this->external_property()->get();
        return $internal_property->merge($external_property)->filter();
    }

    public function getPropertyCountAttribute()
    {
        return $this->properties->count();
    }

    public function internal_property()
    {
        return $this->hasMany(SentInternalPropertyUser::class, 'search_id', 'id');
    }

    public function external_property()
    {
        return $this->hasMany(SentPropertyUser::class, 'search_id', 'id');
    }
}
