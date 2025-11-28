<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanFromBank extends Model
{
    protected $table = "loan_from_banks";
    protected $guarded = [];

    public function loan_emi()
    {
        return $this->belongsTo(LoanEmi::class,'loan_bank_account','id');
    }

    public function bankDetails()
    {
        return $this->belongsTo(SamraddhBankAccount::class,'received_bank','bank_id');
    }

    public function headDetails()
    {
        return $this->belongsTo(AccountHeads::class,'account_head_id','head_id');
    }
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
}