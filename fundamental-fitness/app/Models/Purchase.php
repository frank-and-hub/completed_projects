<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.purchases');
    }

    protected $fillable = [
        'user_id',
        'product_id',
        'amount',
        'platform',
        'purchase_token',
        'transaction_id',
        'status',
        'transaction_date'
    ];
}
