<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes, ModelTraits;

    protected $table = 'locations';

    protected $fillable = [
        'city',
        'city_slug',
        'state',
        'state_slug',
        'country',
        'country_short_name',
        'title',
        'subtitle',
        'thumbnail_id',
        'banner_id',
        'status',
        'default_container_id',
        'location_latitude',
        'location_longitude',
        'seo_description'
    ];

    public function thumbnail()
    {
        return $this->belongsTo(Media::class, "thumbnail_id");
    }
    public function banner()
    {
        return $this->belongsTo(Media::class, "banner_id");
    }

    public function containers()
    {
        return $this->hasMany(Container::class);
    }

    public function default_container()
    {
        return $this->belongsTo(Container::class, "default_container_id");
    }

    public function scopeSearch($query, $term)
    {
        $normalizedTerm = $this->normalizeString($term);

        return $query->where(function ($query) use ($normalizedTerm) {
            $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "&", "and"), " ", "") LIKE ?', ["%{$normalizedTerm}%"])
                ->orWhereRaw('REPLACE(REPLACE(REPLACE(REPLACE(name, "-", ""), ".", ""), "and", "&"), " ", "") LIKE ?', ["%{$normalizedTerm}%"]);
        });
    }

    protected function normalizeString($string)
    {
        return str_replace(['-', '.', '&', ' '], ['', '', 'and', ''], strtolower($string));
    }

    public function getSeoDescriptionAttribute($value)
    {
        return $value ?? $this->generateAndSaveSeoDescription();
    }

    // public function getParksCountAttribute()
    // {
    //     return Parks::where('city', $this->city)
    //         ->where('state', $this->state)
    //         ->where('country', $this->country)
    //         ->count();
    // }
}
