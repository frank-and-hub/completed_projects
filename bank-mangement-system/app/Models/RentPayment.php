<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentPayment extends Model
{
    protected $table = "rent_payments";
    protected $guarded = [];  


    public function rentLib() {
        return $this->belongsTo(RentLiability::class,'rent_liability_id');
    }

    public function rentBranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function rentBank() {
        return $this->belongsTo(SamraddhBank::class,'company_bank_id');
    }
    public function rentBankAccount() {
        return $this->belongsTo(SamraddhBankAccount::class,'company_bank_ac_id');
    }
    public function rentSSB() {
        return $this->belongsTo(SavingAccount::class,'owner_ssb_id');
    }

    public function rentEmp() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
     public function rentLibL() {
        return $this->belongsTo(RentLiability::class,'rent_liability_id');
    }
    public function rentCompany(){
        return $this->belongsTo(Companies::class , 'company_id');
    }
}