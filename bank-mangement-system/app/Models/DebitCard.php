<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitCard extends Model {
    protected $table = "debit_card";
    protected $guarded = [];

    public function savingAccount ()
    {
        return $this->belongsto(SavingAccount::class,'ssb_id')->orderBy('created_at', 'desc');
    }
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
}
