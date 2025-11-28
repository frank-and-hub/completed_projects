<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedCheque extends Model
{
    protected $table = "received_cheques";
    protected $guarded = [];  
    
    public function receivedBank() {
        return $this->belongsTo(SamraddhBank::class,'deposit_bank_id');
    }
    public function receivedAccount() {
        return $this->belongsTo(SamraddhBankAccount::class,'deposit_account_id');
    }
    
     public function receivedChequePayment() {

        return $this->belongsTo(ReceivedChequePayment::class,'id','cheque_id');

    }

    public function receivedBranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function receivedCompany() {
        return $this->belongsTo(Companies::class,'company_id');
    }
}
 