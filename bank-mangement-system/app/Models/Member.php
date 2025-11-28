<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = "members";
    protected $guarded = [];

    /******* Relationship with investment table *******/
    public function investment()
    {
        return $this->belongsTo(Memberinvestments::class);
    }
    public function associateCode()
    {
        return $this->belongsTo(self::class, 'associate_code', 'associate_no');
    }
    public function investments()
    {
        return $this->belongsTo(Memberinvestments::class, 'member_id', 'id');
    }

    public function children()
    {
        return $this->belongsTo(self::class, 'associate_id', 'id');
    }

    public function seniorData()
    {
        return $this->belongsTo(self::class, 'associate_senior_id', 'id');
    }
    /******* Relationship with saving_account table *******/
    public function savingAccount()
    {
        return $this->hasMany(SavingAccount::class, 'customer_id');
    }

    public function memberCompany()
    {
        return $this->belongsTo(MemberCompany::class, 'id', 'customer_id');
    }

    //Relationship with member_nominees table
    public function memberNominee()
    {
        return $this->hasMany(MemberNominee::class, 'member_id');
    }

    //Relationship with member_bank_details table
    public function memberBankDetails()
    {
        return $this->hasMany(MemberBankDetail::class, 'member_id');
    }

    //Relationship with member_bank_details table
    public function memberIdProofs()
    {
        return $this->hasOne(MemberIdProof::class, 'member_id');
    }

    public function memberIdProof()
    {
        return $this->belongsTo(MemberIdProof::class, 'id', 'member_id')->where('is_deleted',0);
    }
    public function memberNomineeDetails()
    {
        return $this->belongsTo(MemberNominee::class, 'id', 'member_id');
    }


    //Relationship with member_bank_details table
    public function associateInvestment()
    {
        return $this->hasMany(Memberinvestments::class, 'member_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    //Relationship with member_bank_details table
    public function associateCommission()
    {
        return $this->hasMany(AssociateCommission::class, 'member_id');
    }

    //Relationship with member_bank_details table
    public function memberReinvestment()
    {
        return $this->hasMany(ReinvestData::class, 'account_number', 'reinvest_old_account_number');
    }

    public function memberReinvestmentcheckExist()
    {
        return $this->belongsTo(ReinvestData::class, 'account_number', 'reinvest_old_account_number')->count();
    }

    public function associate_branch()
    {
        return $this->belongsTo(Branch::class, 'associate_branch_id');
    }


    public function states()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }


    public function correctionRequestRelation()
    {
        return $this->hasMany(CorrectionRequests::class, 'correction_type_id', 'id')->where('correction_type', '=', 0);
    }

    public function getCarderNameCustom()
    {
        return $this->belongsTo(Carder::class, 'current_carder_id');
    }


    public function AssociateTotalCommission()
    {
        return $this->hasMany(AssociateCommission::class, 'member_id')->where('is_distribute', 1)->where('type', '>', 2);
    }

    public function savingAccount_Custom()
    {
        return $this->belongsTo(SavingAccount::class, 'id', 'member_id');
    }
    public function savingAccount_CustomAssociate()
    {
        return $this->belongsTo(SavingAccount::class, 'id', 'associate_id');
    }

    public function savingAccount_Custom2()
    {
        return $this->hasMany(SavingAccount::class, 'id', 'customer_id');
    }

    public function savingAccount_Custom3()
    {
        return $this->belongsTo(SavingAccount::class, 'id', 'customer_id');
    }

    public function savingAccount_Customnew()
    {
        return $this->hasMany(SavingAccount::class, 'customer_id', 'id');
    }


    public function getBusinessTargetAmt()
    {
        return $this->belongsTo(BusinessTarget::class, 'current_carder_id');
        //$businessTarget = App\Models\BusinessTarget::select('credit','self')->where('carder_id',$carderid)->first();
        return $businessTarget;
    }


    public function savingAccountGroupLoanCustom()
    {
        return $this->belongsTo(SavingAccount::class, 'id', 'member_id');
    }

    public function memberinvestments()
    {
        return $this->hasMany(Memberinvestments::class, 'member_id', 'id')->where('is_mature', 1);
    }
    public function memberloans()
    {
        return $this->belongsTo(Memberloans::class, 'type_id');
    }
    public function grouploans()
    {
        return $this->belongsTo(Grouploans::class, 'type_id');
    }

    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function customerAssocaite()
    {
        return $this->belongsTo(Member::class, 'associate_id', 'id');
    }

    public function customerInvestment()
    {
        return $this->hasMany(Memberinvestments::class, 'customer_id');
    }

    public function occupation()
    {
        return $this->belongsto(Occupation::class, 'occupation_id');
    }


    public function checkMemberLoanExist()
    {
        return $this->belongsTo(Memberloans::class, 'customer_id', 'id')->whereNotIn('status', [3, 5])->where('is_deleted', 0);
    }

    public function checkMemberLoanAgainstExist()
    {
        return $this->belongsTo(Memberloans::class, 'customer_id', 'id');
    }
    public function memberCurrentLoan()
    {
        return $this->belongsTo(Memberloans::class, 'id', 'customer_id')->whereNotIn('status', [3, 5])->where('is_deleted', 0);
    }

    public function memberCompanyRecord()
    {
        return $this->hasMany(MemberCompany::class, 'customer_id', 'id');
    }
    /** new age attributes added by sourab on 04-01-2024 for getting member age */
    public function getAgeAttribute()
    {
        return ($this->attributes['age']) ?? calculateAge(date('Y-m-d', strtotime(convertdate($this->attributes['dob']))));
    }
    public function blackListData()
    {
        return $this->belongsTo(MemberLog::class, 'id', 'customer_id')->orderby('id', 'desc');
    }
    public function bankDetails()
    {
        return $this->hasOne(MemberBankDetail::class, 'member_id', 'id');
    }
    public function getEmployeeDetails()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');

    }
    public function ledgerListing()
    {
        return $this->hasManyThrough(
            BranchDaybook::class,     // The final related model you want to access
            MemberCompany::class,     // The intermediate model (pivot)
            'customer_id',              // The foreign key on the intermediate model (MemberCompany)
            'member_id',            // The foreign key on the final model (BranchDaybook)
            'id',                     // The local key on the initial model (Member)
        );
    }
    public function memberCompanies()
    {
        return $this->hasMany(MemberCompany::class, 'member_id', 'id');
    }

    public function checkMemberLoanAgainstExistNew()
    {
        return $this->belongsTo(Memberloans::class,'id', 'customer_id' );
    }
}
