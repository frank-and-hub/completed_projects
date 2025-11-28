<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamraddhBankDaybook extends Model
{
    protected $table = "samraddh_bank_daybook";
    protected $guarded = [];

    public function Branch ()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function getCheque()
    {
        return $this->belongsTo(ReceivedCheque::class,'cheque_id','id');
    }
    public function companyName(){
        return $this->hasOne(Companies::class ,'id','company_id')->withoutGlobalScopes();
    }
    public function memberCompany(){
        return $this->belongsTo(MemberCompany::class ,'member_id','id');
    }
    public function memberInvestment(){
        return $this->belongsTo(Memberinvestments::class ,'member_id','member_id');
    }
    public function SamraddhBankAccount(){
        return $this->belongsTo(SamraddhBankAccount::class ,'bank_id','bank_id');
    }
}
