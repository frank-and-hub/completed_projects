<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scholarship\Scholarship;


class Resource extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function Scholarship(){
        return $this->belongsTo(Scholarship::class,'scholarship_id','id');
    }

}
