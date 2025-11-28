<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionLeaserDetail extends Model
{
    protected $table = "commission_leaser_details";
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
    	return $this->belongsTo('App\Models\SavingAccount','member_id','member_id');
    }
}
