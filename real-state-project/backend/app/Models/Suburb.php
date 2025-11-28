<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suburb extends Model
{
    use HasFactory,HasUuids;

    protected $table = 'suburb';
    protected $fillable = ['province_id','city_id','suburb_name'];
    
    public function province(){
        return $this->belongsTo(Province::class,'province_id');
    }
    public function city(){
        return $this->belongsTo(City::class,'city_id');
    }
}
