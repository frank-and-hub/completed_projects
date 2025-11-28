<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkFeature extends Model
{
    protected $table = 'park_features';

    protected $fillable = ['park_id', 'feature_id', 'feature_type_id', 'active'];

    public function feature_type()
    {
        return $this->belongsTo(FeatureType::class, "feature_type_id");
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class, "feature_id");
    }

    public function parks()
    {
        return $this->belongsTo(Feature::class, "park_id");
    }
}
