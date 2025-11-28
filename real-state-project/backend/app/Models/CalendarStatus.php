<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarStatus extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'calendar_id',
        'user_id',
        'admin_id',
        'status',
        'description'
    ];
}
