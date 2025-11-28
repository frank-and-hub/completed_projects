<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptAmount extends Model
{
    protected $table = "receipt_amounts";
    protected $guarded = [];

    public function receiptAmount() {
        return $this->belongsTo(Receipt::class,'id');
    }
}
