<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    protected $table = "plans";
    protected $guarded = [];

    /******* Relationship with investment table *******/
    public function investment()
    {
        return $this->belongsTo(Memberinvestments::class);
    }
    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'created_by_id');
    }
    public function PlanTenures()
    {
        return $this->hasMany(PlanTenures::class, 'plan_id', 'id')->whereColumn('tenure','month_from');
    }
    public function MoneyBack()
    {
        return $this->hasMany(MoneyBackSetting::class, 'plan_id', 'id');
    }
    public function DeathHelpSettin()
    {
        return $this->hasMany(DeathHelpSetting::class, 'plan_id', 'id');
    }
    public function LoanAgainst()
    {
        return $this->hasMany(LoanAgainstDeposit::class, 'plan_id', 'id');
    }
    public function PlanDeno()
    {
        return $this->hasMany(PlanDenos::class, 'plan_id', 'id');
    }
    public function DepositHeadName()
    {
        return $this->hasMany(AccountHeads::class, 'head_id', 'deposit_head_id');
    }
    public function InterestHeadName()
    {
        return $this->hasMany(AccountHeads::class, 'head_id', 'interest_head_id');
    }
    public function CategoryName()
    {
        return $this->hasMany(PlanCategory::class, 'code', 'plan_category_code');
    }
    public function SubCategoryName()
    {
        return $this->hasMany(PlanCategory::class, 'code', 'plan_sub_category_code');
    }
    function getNameAttribute()
    {
        return ucwords($this->attributes['name']);
    }
    function getShortNameAttribute()
    {
        return ucwords($this->attributes['short_name']);
    }
    public function company()
    {
        return $this->belongsTo(Companies::class);
    }
    public function planTenuresWithoutSsb()
    {
        return $this->hasMany(PlanTenures::class, 'plan_id', 'id')
            ->where('plan_category_code', '<>', 'S');
    }
	/*
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveScope);
    }
	*/
    public function plantenure()
    {
        return $this->hasOne(PlanTenures::class, 'plan_id', 'id');
    }
    public function Commissiondetail()
    {
        return $this->hasMany(CommissionDetail::class, 'plan_id','id');
    }
}