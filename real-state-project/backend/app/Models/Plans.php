<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plans extends Model
{
    use HasFactory , HasUuids;
    protected $table= 'plans';
    protected $fillable = [
        'plan_name',
        'amount',
        'type',
        'status',
    ];

    public function usersubscription()
    {
        return $this->hasMany(Plans::class,'subscription_id');
    }

    public function user_search_property(): HasManyThrough
    {
        return $this->hasManyThrough(UserSearchProperty::class, UserSubscription::class, 'subscription_id', 'user_subscription_id', 'id', 'id');
    }

    public function planfeatures()
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }
}
