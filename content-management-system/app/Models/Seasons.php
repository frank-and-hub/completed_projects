<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seasons extends Model
{
    use HasFactory;
    protected $fillable = ['hemisphere', 'season', 'start_date', 'end_date','season_start_date','season_end_date'];
    // protected $casts = [
    //     'start_date' => 'M d',
    //     'end_date' => 'M d',
    // ];

    // protected function startDate(): Attribute
    // {
    //     return  Attribute::make(
    //         // set: fn ($val) => Carbon::parse($val)->format('Y-m-d'),
    //         get: fn ($val) => Carbon::parse($val)->format('d-M-Y')

    //     );
    // }
    // protected function endDate(): Attribute
    // {
    //     return Attribute::make(
    //         // set: fn ($val) => Carbon::parse($val)->format('Y-m-d'),
    //         get: fn ($val) => Carbon::parse($val)->format('d-M-Y'),
    //     );
    // }
}
