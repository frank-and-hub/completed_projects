<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLeaserMonthly extends Model
{
    protected $table = 'commission_leaser_monthly';
    protected $guarded = [];


    public function ledgerCompany()
    {
    	return $this->belongsTo('App\Models\Companies','company_id','id'); 
    }
}
