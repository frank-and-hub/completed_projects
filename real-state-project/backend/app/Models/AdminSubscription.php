<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admin_subscription';
    protected $fillable = [
        'admin_id',
        'plan_name',
        'subscription_id',
        'amount',
        'status',
        'total_property',
        'can_add_property',
        'expired_at',
        'pf_payment_id',
        'amount_gross',
        'amount_fee',
        'amount_net'
    ];

    protected $casts = [
        'expired_at' => "datetime"
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    public function plan()
    {
        return $this->belongsTo(Plans::class, 'subscription_id');
    }


}
