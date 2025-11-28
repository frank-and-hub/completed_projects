<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $table = "receipts";
    protected $guarded = [];

    public function memberReceipt() {
        return $this->belongsTo(Member::class,'member_id');
    }
    public function branchReceipt() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function receiptAmount() {
        return $this->hasMany(ReceiptAmount::class);
    }
}
