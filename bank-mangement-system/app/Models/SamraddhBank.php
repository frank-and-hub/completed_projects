<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamraddhBank extends Model
{
    protected $table = "samraddh_banks";
    protected $guarded = [];  

    public function bankAccount(){
        return $this->belongsTo(SamraddhBankAccount::class,'id','bank_id');
    }
	public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }

    public function allBankAccount(){
        return $this->hasMany(SamraddhBankAccount::class,'bank_id','id');
    }

    public function samraddhBankCheque(){
        return $this->hasMany(SamraddhCheque::class,'bank_id','id');
    }
	
}
