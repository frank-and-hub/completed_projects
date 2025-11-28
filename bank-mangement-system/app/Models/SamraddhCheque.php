<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamraddhCheque extends Model
{
    protected $table = "samraddh_cheques";
    protected $guarded = [];  
    
    public function samrddhBank() {
        return $this->belongsTo(SamraddhBank::class,'bank_id');
    }
    public function samrddhAccount() {
        return $this->belongsTo(SamraddhBankAccount::class,'account_id');
    }
}
 