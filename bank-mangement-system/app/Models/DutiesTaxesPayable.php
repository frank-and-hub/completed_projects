<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutiesTaxesPayable extends Model {
    protected $table = "duties_taxes_payables";
    protected $guarded = [];
    public function challan()
    {
        return $this->belongsTo(Files::class,'challan_id','id');
    }
    public function accountHead()
    {
        return $this->hasOne(AccountHeads::class, 'head_id', 'head_id');
    }

    public function bank()
    {
        return $this->belongsTo(SamraddhBank::class, 'bank_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(SamraddhBankAccount::class, 'account_id');
    }

    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
}
