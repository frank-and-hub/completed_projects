<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'description',
        'location_id',
        'image_id',
    ];

    protected $table = 'containers';

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function parks()
    {
        return $this->belongsToMany(Parks::class, 'container_park', 'container_id', 'park_id')->withTimestamps();
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
    }

    public function getParksIdAttribute()
    {
        return $this->parks->pluck('id')->toArray();
    }

    public function feature()
    {
        return $this->belongsToMany(Feature::class, ContainerFeature::class, 'container_id', 'feature_id')->withTimestamps();
    }

    public function feature_type()
    {
        return $this->belongsToMany(FeatureType::class, ContainerFeatureType::class, 'container_id', 'feature_type_id')->withTimestamps();
    }
}
