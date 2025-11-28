<?php

namespace App\Models;

use App\Traits\ModelTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use ModelTraits;

    protected $table = 'features';

    protected $fillable = ['name', 'feature_type_id', 'type', 'image_id', 'priority', 'slug', 'active'];

    public function feature_type()
    {
        return $this->belongsTo(FeatureType::class, "feature_type_id");
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
    }

    public function featureType()
    {
        return $this->belongsToMany(FeatureType::class, ParkFeature::class, 'feature_id', 'feature_type_id');
    }

    public function parkfeatures()
    {
        return $this->hasMany(ParkFeature::class, 'feature_id');
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
        return $this->belongsToMany(Parks::class, ParkFeature::class, 'feature_id', 'park_id');
    }

    public function meta()
    {
        return $this->morphOne(Meta::class, 'metable');
    }

    public function container()
    {
        return $this->belongsToMany(Container::class, ContainerFeature::class, 'feature_id', 'container_id')
            ->withTimestamps();
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
