<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandAdvice extends Model
{
    protected $table = "demand_advices";
    protected $guarded = [];  

    public function expenses() {
        return $this->hasMany(DemandAdviceExpense::class, 'demand_advice_id','id');
    }

    public function expensess() {
        return $this->belongsTo(DemandAdviceExpense::class, 'demand_advice_id','id');
    }

    public function branch() {
        return $this->hasOne(Branch::class, 'id','branch_id');
    }

    public function investment() {
        return $this->hasOne(Memberinvestments::class, 'id','investment_id');
    }

    public function employee() {
        return $this->hasOne(Employee::class, 'id','employee_id');
    }

    public function owner() {
        return $this->hasOne(RentLiability::class, 'id','owner_id');
    }

    public function demandAmount() {
        return $this->hasOne(AllHeadTransaction::class, 'type_id','id')->where('type',13)->whereIn('sub_type',[136,133,134,137]);
    }
    public function demandAmountHead() {
        return $this->hasOne(AllHeadTransaction::class, 'type_id','id')->where('head_id',92);
    }

    public function demandTransactionAmount() {
        return $this->hasOne(AllHeadTransaction::class, 'type_id','id')->where('type',13);
    }

    public function getFileDataCustom() {
        return $this->belongsTo(Files::class, 'letter_photo_id');
    }

    public function sumdeposite() {
        return $this->hasMany(Daybook::class,'investment_id','id')->whereIn('transaction_type',[2,4]);
    }

    public function sumdeposite2() {
        return $this->hasMany(Daybook::class,'investment_id','investment_id')->whereIn('transaction_type',[2,4])->where('is_deleted',0);
    }

    public function openingBalance() {
        return $this->hasMany(Daybook::class,'investment_id','investment_id');
    }
    public function getCurrentBalanceAttribute()
    {
        return $this->sumdeposite2()->groupBy('investment_id')->sum('deposit') ?? 0;
    }
    public function demandReason()
    {
        return $this->hasMany(RedemandDemandAdvice::class,'demand_id','id');
    }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
    public function memberCompany() {
        return $this->belongsTo(MemberCompany::class,'member_id','id');
    }
    public function member() {
        return $this->belongsTo(Member::class,'customer_id','id');
    }

    public function getCurrentBalanceAttributeNew()
    {
        $depositebance =  $this->openingBalance()->where('is_deleted',0)->where('transaction_type','<>',19)->groupBy('investment_id')->sum('deposit') ?? 0;
        $withdrawal  =  $this->openingBalance()->where('is_deleted',0)->where('transaction_type','<>',19)->groupBy('investment_id')->sum('withdrawal') ?? 0;
        return ($depositebance ?? 0)  - ($withdrawal ?? 0);
    }
    public function getBalanceAttribute(){
        $data = InvestmentBalance::where('investment_id',$this->attributes['investment_id'])->value('totalBalance');
        return $data;
    }
}
 