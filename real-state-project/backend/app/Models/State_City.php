<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State_City extends Model
{
    use HasFactory;

    protected $table = 'state_cities';

    protected $fillable = [
        'id',
        'state_id',
        'city_name',
        'country_id',
        'created_at',
        'updated_at'
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'name', 'city_name');
    }
}
