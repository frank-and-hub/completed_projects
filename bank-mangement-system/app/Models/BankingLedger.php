<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankingLedger extends Model {
    protected $table = "banking_ledger";
    protected $guarded = [];

    public function relatedRecord() {
        return $this->hasMany(BankingDueBillsLedger::class,'banking_id');
    }

    public function relatedAdvancedRecord() {
        return $this->hasMany(BankingAdvancedLedger::class,'banking_id');
    }

}
