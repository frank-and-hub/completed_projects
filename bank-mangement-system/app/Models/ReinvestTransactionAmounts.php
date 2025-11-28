<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReinvestTransactionAmounts extends Model
{
	protected $table = "reinvest_transaction_amounts";
	protected $fillable = ['investment_id', 'member_id', 'date', 'amount'];
}
