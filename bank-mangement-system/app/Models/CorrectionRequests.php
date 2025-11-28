<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorrectionRequests extends Model {
    protected $table = "correction_requests";
    protected $guarded = [];

    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function correctionDaybookCustom(){
        return $this->belongsTo(Daybook::class,'correction_type_id');
    }

    public function correctionSeniorCustom(){
        return $this->belongsTo(Member::class,'correction_type_id');
    }

    public function correctionMemberInvestmentCustom(){
        return $this->belongsTo(Memberinvestments::class,'correction_type_id');
    }

    public function correctionSavingAccount(){
        return $this->belongsTo(SavingAccountTranscation::class,'correction_type_id');
    }

    public function correctionCompay(){
        return $this->belongsTo(Companies::class,'company_id','id');
    }
    public function MemberInvestment(){
        return $this->belongsTo(Memberinvestments::class,'account_id');
    }
}
