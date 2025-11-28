<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model
{
    protected $table = 'cron_logs';
    protected $primaryKey = 'id';
    protected $guarded = [];
    
}
