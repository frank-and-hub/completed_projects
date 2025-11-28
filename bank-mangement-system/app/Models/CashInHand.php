<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 
class CashInHand extends Model
{
    protected $table = "branch_cash";
    protected $guarded = [];

    public function cashBranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
}
