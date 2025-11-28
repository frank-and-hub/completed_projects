<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Companies;

class Loans extends Model
{
    protected $table = "loans";
    protected $guarded = [];

    public function companies()
    {
        return $this->hasOne(Companies::class,'id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class);
    }
}
