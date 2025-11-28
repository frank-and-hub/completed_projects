<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateCommissionTotalMonthly extends Model
{
    protected $table = "associate_commissions_total_monthly";
    protected $guarded = [];  


    public function comm_member()
    {
        return $this->belongsTo(Member::class,'member_id','id');
    }
    public function tds_member()
    {
        return $this->belongsTo(AssociateTdsDeduct::class,'member_id','member_id');
    }
    
}
