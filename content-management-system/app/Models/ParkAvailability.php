<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkAvailability extends Model
{
    protected  $table="park_availabilities";

    protected $fillable = ['park_id','day','opening_time','closing_time','type'];

    // protected $casts =[
    //     'opening_time' => 'dateTime',
    //     'closing_time' => 'dateTime'
    // ];
}
