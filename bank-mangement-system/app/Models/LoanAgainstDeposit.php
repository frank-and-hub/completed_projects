<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LoanAgainstDeposit extends Model {
    use SoftDeletes;
    protected $table = "loan_against_deposits";
    protected $guarded = [];

}

