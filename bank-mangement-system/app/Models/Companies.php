<?php

namespace App\Models;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\Loan;

class Companies extends Model
{

    protected $table = "companies";

    protected $guarded = []; 
    public function company()
    {
        return $this->belongsTo(Plans::class,'company_id','id');
    }
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveScope);
    }

    public function getNameAttribute($value)
    {
        return $this->attributes['name'] = strtoupper($value);
    }

     public function scopeGetCompany($query,$status='')
    {
        return $query->when($status,function($q) use($status){
            $q->whereStatus($status);
        });
    }    
	public function branches()
    {
        return $this->belongsToMany(Branch::class, 'company_branch', 'company_id', 'branch_id');
    }
	public function companybranchs(){
		return $this->belongsTo(CompanyBranch::class,'id','company_id');
	}

    //relation create with loans table
    public function loans()
    {
        return $this->hasMany(Loans::class,'company_id');
    }
    // ======= Plans
    public function plans()
    {
        return $this->hasMany(Plans::class,'company_id');
    }
    // relation created with saving account
    public function ssbAccount()
    {
        return $this->hasMany(SavingAccount::class,'company_id');
    }
    public function companyAssociate(){
        return $this->hasMany(CompanyAssociate::class,'company_id');
    }
    public function fa_code(){
        return $this->hasMany(FaCode::class,'company_id');
    }
    public function memberCompany(){
        return $this->hasMany(MemberCompany::class,'company_id');
    }    
    public function grouploan(){
        return $this->hasMany(Grouploans::class,'company_id');
    }    
}
