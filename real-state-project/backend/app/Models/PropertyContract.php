<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyContract extends Model
{
    use HasFactory,HasUuids;

    protected $table = "property_contract";
    protected $guarded = [];
}
