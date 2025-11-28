<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureType extends Model
{
    use ModelTraits;

    protected $table = 'feature_types';

    protected $fillable = ['name', 'image_id', 'active', 'type', 'priority'];

    public function features()
    {
        return $this->hasMany(Feature::class, "feature_type_id");
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
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

    public function parks()
    {
        return $this->hasManyThrough(
            Parks::class,
            ParkFeature::class,
            'feature_type_id',
            'id',
            'id',
            'park_id'
        );
    }

    public function meta()
    {
        return $this->morphOne(Meta::class, 'metable');
    }

    public function seo()
    {
        return $this->morphOne(SeoDescription::class, 'metable');
    }

    public function getSeoDescriptionAttribute()
    {
        return $this?->seo?->description ?? $this->generateAndStoreFeatureSeoDescription();
    }
}
