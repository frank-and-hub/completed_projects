<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model {
    protected $table = "branch";
    protected $guarded = [];

    public function reciptBranch()
	{
		return $this->belongsTo(Receipt::class,'branch_id');
	}

	public function branchCityCustom()
	{
		return $this->belongsTo(City::class,'city_id');
	}

	public function branchStatesCustom()
	{
		return $this->belongsTo(States::class,'state_id');
	}
	public function companies()
    {
        return $this->belongsToMany(Companies::class,'company_branch','branch_id','company_id');
    }
	public function companybranchs(){
		return $this->belongsTo(CompanyBranch::class,'id','branch_id');
	}

	public function companies_branch()
    {
		return $this->HasMany('App\Models\CompanyBranch','branch_id','id')->where('status','1')->select('branch_id','company_id','is_primary','is_new_business','is_old_business');
    }

	public function companybranchsAll(){
		return $this->hasMany(CompanyBranch::class,'branch_id','id');
	}
	public function branchLog(){
		return $this->hasMany(BranchLog::class,'id');
	}
}
