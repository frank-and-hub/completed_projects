<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBound extends Model
{
    protected $table = "company_bound";
    protected $guarded = [];  

    public function fdSamraddhBank(){
        return $this->belongsTo(SamraddhBank::class,'rec_bank');
    }

    public function fdSamraddhBankAccountId(){
        return $this->belongsTo(SamraddhBankAccount::class,'rec_bank_account');
    }

    public function getCompanyBoundTransaction(){
        return $this->belongsTo(CompanyBoundTransaction::class,'id','bound_id');
    }

    public function getCompanyBoundTransactionV2(){
        return $this->hasMany(CompanyBoundTransaction::class,'bound_id','id');
    }
   
    public function companies(){
        return $this->belongsTo(Companies::class,'company_id');
    }
}
