<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScheduleTime extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'user_schedule_time';
    protected $fillable = ['user_id', 'start_time', 'end_time', 'schedule_type', 'user_subscription_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }
}
