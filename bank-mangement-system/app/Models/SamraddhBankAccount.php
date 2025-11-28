<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamraddhBankAccount extends Model
{
    protected $table = "samraddh_bank_accounts";
    protected $guarded = [];  

    public function accountHeads(){
        return $this->belongsTo(AccountHeads::class,'account_head_id','head_id');
    }
    // Created By Vishwajeet 0n 02-02-2023 to call bank name
    public function getBankName(){
        return $this->hasone(SamraddhBank::class, 'account_head_id','account_head_id');
    }
    public function getCompanyDetail(){
        return $this->hasMany(Companies::class, 'id','company_id');
    }
    public function samraddhbank(){
        return $this->hasone(SamraddhBank::class, 'id','bank_id');
    }
}
