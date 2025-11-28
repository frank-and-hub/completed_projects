<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBranch extends Model
{
    protected $table = "company_branch";

    protected $guarded = []; 
 
	public function company()
    {
        return $this->hasMany(Companies::class,'id','company_id');
    }
	public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function get_company()
    {
        return $this->belongsTo(Companies::class,'company_id');
    }
	
}