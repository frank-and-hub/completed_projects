<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'user_subscription';
    protected $fillable = [
        'user_id',
        'subscription_id',
        'pf_payment_id',
        'amount',
        'amount_fee',
        'amount_net',
        'status',
        'total_request',
        'is_active',
        'started_at',
        'expired_at'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ONGOING = 'ongoing';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function plan()
    {
        return $this->belongsTo(Plans::class, 'subscription_id');
    }

    public function user_search_property()
    {
        return $this->hasMany(UserSearchProperty::class, 'user_subscription_id');
    }

    /**
     * Get the user_schedule_time that owns the UserSubscription
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user_schedule_time()
    {
        return $this->hasOne(UserScheduleTime::class, 'user_subscription_id', 'id');
    }

    public function getNoOfRequestAttribute()
    {
        $data = UserSearchProperty::select('id', 'user_id', 'created_at')->whereUserId($this->user_id)
            ->whereBetween('created_at', [
                $this->started_at,
                $this->expired_at
            ])->count('id');
        return $data;
    }
}
