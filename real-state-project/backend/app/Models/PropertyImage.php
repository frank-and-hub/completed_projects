<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'properties_images';
    protected $fillable = [
        'properties_id','clientPropertyID', 'imgUrl', 'imgDescription', 'isMain'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'properties_id');
    }
}
