<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentLiability extends Model {
    protected $table = "rent_liabilities";
    protected $guarded = [];

    public function liabilityBranch()
    {
        return $this->hasOne('App\Models\Branch', 'id','branch_id');
    }

    public function liabilityFile()
    {
        return $this->hasOne('App\Models\Files', 'id','rent_agreement_file_id');
    }
    public function employee_rent() {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function AcountHeadCustom() {
        return $this->belongsTo(AccountHeads::class,'rent_type');
    }

    public function SsbAccountNumberCustom() {
        return $this->belongsTo(SavingAccount::class,'owner_ssb_id');
    }

    public function rentFileCustom() {
        return $this->belongsTo(Files::class,'rent_agreement_file_id');
    }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
    
    public function getssbaccountnumber() {
        return $this->belongsTo(SavingAccount::class,'employee_id');
    }
    public function advance() {
        return $this->hasMany(AdvancedTransaction::class,'type_id')->where('type',3);
    }
}
