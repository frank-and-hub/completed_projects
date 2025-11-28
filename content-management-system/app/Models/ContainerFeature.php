<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerFeature extends Model
{
    use HasFactory;

    protected $table = "container_features";

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
