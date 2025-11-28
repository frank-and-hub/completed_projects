<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Parks extends Model
{
    protected $table = 'parks';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'country',
        'country_short_name',
        'city',
        'city_slug',
        'state',
        'state_slug',
        'image_ids',
        'description',
        'url',
        'is_paid',
        'instructions',
        'instruction_url',
        'ticket_amount',
        'timezone',
        'active',
        'search_slug',
    ];

    protected $casts = [
        'image_ids' => 'array'
    ];

    public function categories()
    {
        return $this->hasMany(ParkCategories::class, "park_id");
    }

    public function subcategory()
    {
        return $this->belongsToMany(Subcategory::class, ParkCategories::class, "park_id", 'subcategory_id');
    }

    public function features()
    {
        return $this->hasMany(ParkFeature::class, "park_id");
    }

    public function featuresType()
    {
        return $this->belongsToMany(FeatureType::class, ParkFeature::class, "park_id", 'feature_type_id');
    }

    public function park_images()
    {
        return $this->hasMany(ParkImage::class, "park_id");
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'park_id');
    }

    public function park_availability()
    {

        return $this->hasMany(ParkAvailability::class, 'park_id');
    }

    public function scopeHavingDistance($query, $lat, $long, $distance = null)
    {
        return $query->select([
            'parks.*',
            DB::raw('ROUND((6371 * acos( cos( radians(' . $lat . ') ) * cos( radians(parks.latitude ) ) * cos( radians(parks.longitude ) - radians(' . $long . ') ) + sin( radians(' . $lat . ') ) * sin( radians(parks.latitude ) ) ) ),2) as distance'),
        ])->having('distance', '<=', $distance ?? config('constants.default_radius'));
    }

    public function bookmark(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'park_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function allParkImagesUsers()
    {
        return $this->belongsToMany(User::class, 'park_images', 'park_id', 'user_id', 'id', 'id');
    }

    // Add a scope to handle the search logic
    public function scopeSearch($query, $term)
    {
        $normalizedTerm = $this->normalizeString($term);

        return $query->where(function ($query) use ($normalizedTerm) {
            $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "&", "and"), " ", "") LIKE ?', ["%{$normalizedTerm}%"])
                ->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "and", "&"), " ", "") LIKE ?', ["%{$normalizedTerm}%"]);
        });
    }

    // Helper function to normalize the search term
    protected function normalizeString($string)
    {
        return str_replace(['-', '.', '&', ' '], ['', '', 'and', ''], strtolower($string));
    }

    public function container()
    {
        return $this->belongsToMany(Location::class, 'container_park', 'park_id', 'container_id');
    }
}
