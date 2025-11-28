<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\CountryData\District;

class AddressDetail extends Model
{
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function distict(){
        return $this->belongsTo(District::class, 'district','id');

    }
}
