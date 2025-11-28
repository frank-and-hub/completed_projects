<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPropertyMedia extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'internal_property_id',
        'path',
        'isMain',
        'media_type',
        'description'
    ];

    public function property()
    {
        return $this->belongsTo(InternalProperty::class, 'internal_property_id');
    }
}
