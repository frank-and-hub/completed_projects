<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_subscriptions');
    }
    
    protected $fillable = [
        'user_id',
        'subscription_id',
        'subscription_plan',
        'payment_gateway',
        'expires_at',
        'subscribed_at',
        'is_recurring',
        'status',
        'is_expire_email_sent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'subscribed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function package()
    {
        return $this->belongsTo(SubscriptionPackages::class, 'subscription_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id', 'id');
    }
    public function purchaseLogs()
    {
        return $this->hasMany(PurchaseLog::class, 'user_id', 'user_id');
    }
}