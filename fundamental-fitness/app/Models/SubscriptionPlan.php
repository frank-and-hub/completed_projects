<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscription_plans');
    }

    protected $fillable = [
        'name',
        'product_id',
        'price',
        'platform',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
