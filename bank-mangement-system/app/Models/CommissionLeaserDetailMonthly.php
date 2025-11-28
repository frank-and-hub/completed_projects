<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLeaserDetailMonthly extends Model
{
    protected $table = 'commission_leaser_detail_monthly';
    protected $guarded = [];

    public function member()
    {
    	return $this->belongsTo('App\Models\Member','member_id','id');
    }

    public function CarderDetail()
    {
    	return $this->belongsTo('App\Models\Carder','member_id','id');
    }

    public function SavingAcount()
    {
    	return $this->hasMany('App\Models\SavingAccount','customer_id','member_id');
    }
    public function commissionLeaser()
    {
    	return $this->belongsTo('App\Models\CommissionLeaserMonthly','commission_leaser_id','id');
    }
    public function company()
    {
    	return $this->hasone('App\Models\Companies','id','company_id')->withoutGlobalScopes();
    }

    
}
