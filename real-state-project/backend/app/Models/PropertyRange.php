<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyRange extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'property_price_range';
    protected $fillable = ['start_price','end_price','currency'];
}
