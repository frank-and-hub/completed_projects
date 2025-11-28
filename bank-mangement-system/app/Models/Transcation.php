<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcation extends Model
{
    protected $table = "transactions";
    protected $guarded = [];

    public function memberTransaction() {
        return $this->belongsTo(Member::class,'member_id');
    }

    public function TransactionBranchDetailCustom() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    
}
