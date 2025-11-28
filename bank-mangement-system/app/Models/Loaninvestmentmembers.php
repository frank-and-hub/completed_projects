<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loaninvestmentmembers extends Model
{
    protected $table = "loan_investment_plans";
    protected $guarded = [];


    public function memberLoans()
    {
        return $this->belongsTo(Memberloans::class,'member_loan_id','id');
    }

    public function loan_investment_plans()
    {
        return $this->belongsTo(Memberinvestments::class,'plan_id','id');
    }
}
