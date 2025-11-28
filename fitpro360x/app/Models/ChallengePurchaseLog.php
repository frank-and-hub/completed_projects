<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengePurchaseLog extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.challenge_purchase_logs');
       
    }

      protected $fillable = [
        'user_id',
        'transaction_id',
        'purchaseReceipt',
        'verification_data',
    ];

    protected $casts = [
        'verification_data' => 'array',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
