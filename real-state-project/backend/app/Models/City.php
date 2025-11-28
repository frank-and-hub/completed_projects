<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'city';
    protected $fillable = ['city_name','province_id', 'country_id'];

    public function province(){
        return $this->belongsTo(Province::class,'province_id');
    }
    public function suburb(){
        return $this->hasMany(Suburb::class,'city_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}