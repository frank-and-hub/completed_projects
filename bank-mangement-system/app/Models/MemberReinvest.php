<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Plans;
use App\Models\Member;

class MemberReinvest extends Model
{
    protected $table = "member_reinvestments";
    protected $guarded = [];

    /******* Relationship with member table *******/
    public function member() {
        return $this->belongsTo(Member::class);
    }

    /******* Relationship with plan table *******/
    public function plan() {
        return $this->belongsTo(Plans::class);
    }

    /******* Relationship with Memberinvestmentsnominees table *******/
    public function investmentNomiees() {
        return $this->hasMany(Memberinvestmentsnominees::class, 'investment_id');
    }

    /******* Relationship with Memberinvestmentspayments table *******/
    public function investmentPayment() {
        return $this->hasMany(Memberinvestmentspayments::class, 'investment_id');
    }

    public function ssb() {
        return $this->hasOne(SavingAccount::class,'member_investments_id');
    }
    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    /******* Relationship with member table *******/
    public function associateMember() {
        return $this->belongsTo(Member::class,'associate_id');
    }
}
