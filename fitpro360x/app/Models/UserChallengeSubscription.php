<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeSubscription extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_challenge_subscription');
    }
    protected $fillable = [
        'user_id',
        'subscription_id',
        'transaction_id',
        'payment_gateway',
        'subscribed_at',
        'is_recurring',
        'status',
    ];

    protected $dates = [
        'subscribed_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(ChallengePackages::class, 'subscription_id');
    }
}
