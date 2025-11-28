<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBoundTransaction extends Model
{
    protected $table = "company_bound_transactions";
    protected $guarded = [];  

    public function company_bounds()
    {
        return $this->belongsTo('App\Models\CompanyBound','bound_id','id');
    }
}
