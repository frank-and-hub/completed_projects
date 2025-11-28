<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTransaction extends Model
{
    protected $table = "member_transaction";
    protected $guarded = [];
    
     public function memberTransaction()
    {
        return $this->belongsTo(Member::class,'member_id','id');
    }
        public function memberTransactionBranch()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
}