<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerFeatureType extends Model
{
    use HasFactory;

    protected $table = "container_feature_types";

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function feature_type()
    {
        return $this->belongsTo(FeatureType::class);
    }
}
