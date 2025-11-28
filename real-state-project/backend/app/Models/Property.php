<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'properties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_office_id',
        'clientPropertyID',
        'trID',
        'currency',
        'price',
        'ratesAndTaxes',
        'levy',
        'landSize',
        'landsizeType',
        'buildingSize',
        'buildingSizeType',
        'propertyType',
        'propertyStatus',
        'country',
        'province',
        'town',
        'suburb',
        'beds',
        'bedroomFeatures',
        'baths',
        'bathroomFeatures',
        'pool',
        'listDate',
        'expiryDate',
        'occupationDate',
        'study',
        'livingAreas',
        'staffAccommodation',
        'carports',
        'garages',
        'petsAllowed',
        'description',
        'propertyFeatures',
        'title',
        'priceUnit',
        'isReduced',
        'isDevelopment',
        'mandate',
        'furnished',
        'openparking',
        'streetNumber',
        'streetName',
        'unitNumber',
        'complexName',
        'latlng',
        'showOnMap',
        'action',
        'vtUrl',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'ratesAndTaxes' => 'decimal:2',
        'levy' => 'decimal:2',
        'landSize' => 'decimal:2',
        'buildingSize' => 'decimal:2',
        'beds' => 'decimal:1',
        'baths' => 'decimal:1',
        'pool' => 'boolean',
        'listDate' => 'date',
        'expiryDate' => 'date',
        'occupationDate' => 'date',
        'study' => 'integer',
        'livingAreas' => 'integer',
        'staffAccommodation' => 'integer',
        'carports' => 'integer',
        'garages' => 'integer',
        'petsAllowed' => 'boolean',
        'isReduced' => 'boolean',
        'isDevelopment' => 'boolean',
        'furnished' => 'boolean',
        'openparking' => 'integer',
        'showOnMap' => 'boolean',
    ];

    public function photos()
    {
        return $this->hasMany(PropertyImage::class, 'properties_id');
    }

    public function contacts()
    {
        return $this->hasMany(PropertyContact::class, 'properties_id');
    }

    /**
     * Get the user that owns the Property
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sentProperties()
    {
        return $this->hasMany(SentPropertyUser::class, 'property_id');
    }

    public function clientOffice()
    {
        return $this->belongsTo(PropertyClientOffice::class, 'client_office_id');
    }

    public function images()
    {
        return $this->media()->where('media_type', 'image');
    }

    public function getLatAttribute()
    {
        if (isset($this->attributes['latlng']) && $this->attributes['latlng']) {
            $latlng = $this->attributes['latlng'];
            $coordinates = explode(',', $latlng);
            return $coordinates[0];
        }
        return null;
    }

    public function getLngAttribute()
    {
        if (isset($this->attributes['latlng']) && $this->attributes['latlng']) {
            $latlng = $this->attributes['latlng'];
            $coordinates = explode(',', $latlng);
            return isset($coordinates[1]) ? $coordinates[1] : null;
        }
        return null;
    }

    public static $filterColumnMap = [
        "pet_friendly" => "petsAllowed",
        "pool" => "pool",
        "fully_furnished" => "furnished",
        "parking" => "openparking",
        "garage" => "garages",
        // "move_in_date" => "occupationDate",
        "province_name" => "province",
        "suburb_name" => "suburb",
        "city" => "town",
        "property_type" => "propertyType",
        "start_price" => "price",
        "end_price" => "price",
        "no_of_bedroom" => "beds",
        "no_of_bathroom" => "baths",
        // "additional_features" => "propertyFeatures",
    ];

    public function propertyTimeSlot()
    {
        return $this->hasOne(PropertyTimeSlot::class, 'property_id', 'id');
    }

}
