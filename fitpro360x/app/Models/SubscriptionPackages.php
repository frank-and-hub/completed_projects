<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackages extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscription_packages');
       
    }
    protected $fillable = [
        'plan_name',
        'type',
        'duration',
        'amount',
        'description',
        'active',
        'status',
        'product_id',
    ];
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function purchaseLogs()
    {
        return $this->hasMany(PurchaseLog::class, 'plan_id', 'id');
    }
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
    public function scopeInactive($query)
    {
        return $query->where('active', 0);
    }

}
