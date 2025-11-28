<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitCardTransaction extends Model {
    protected $table = "debit_card_transaction";
    protected $guarded = [];

    public function debitcard()
    {
        return $this->belongs(DebitCard::class,'debit_card_id');
    }
}
