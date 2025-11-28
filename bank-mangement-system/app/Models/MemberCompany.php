<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MemberCompany extends Model {

    protected $table = 'member_companies';
    protected $guarded = [];
    
    public function member() {
        return $this->belongsTo(Member::class,'customer_id','id');
    }
     

    public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function memberAssociate() {
        return $this->belongsTo(Member::class,'associate_id');
    }

    // member_companies 
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
    public function ssb_detail() {
        return $this->hasOne(SavingAccount::class,'member_id','id');
    }

    public function savingAccount() {
        return $this->hasMany(SavingAccount::class,'member_id');
    }
    public function savingAccountNew() {
        return $this->belongsTo(SavingAccount::class,'id','member_id');
    }

    //member table 
    public function ssb_detailCustomer() {
        return $this->hasMany(SavingAccount::class,'customer_id');
    }

    public function memberIdProof() {
        return $this->belongsTo(MemberIdProof::class,'id','member_id');
    }

    public function memberBankDetails() {
        return $this->hasMany(MemberBankDetail::class,'id','member_id');
    }
    
}
