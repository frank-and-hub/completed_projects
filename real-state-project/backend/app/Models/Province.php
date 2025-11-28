<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'province';
    protected $fillable = ['province_name', 'country_id'];

    public function city()
    {
        return $this->hasMany(City::class, 'province_id');
    }
    public function suburb()
    {
        return $this->hasMany(Suburb::class, 'province_id');
    }
}
