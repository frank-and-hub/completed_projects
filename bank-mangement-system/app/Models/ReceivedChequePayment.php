<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedChequePayment extends Model
{
    protected $table = "received_cheque_payments";
    protected $guarded = [];  
    
    public function receivedcheque() {
        return $this->belongsTo(ReceivedCheque::class,'cheque_id');
    } 
}
 