<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model {
    protected $table = "funds_transfer";
    protected $guarded = [];

	public function file()
	{
		return $this->belongsTo('App\Models\Files', 'bank_slip_id', 'id');
	}

	public function BranchNameByBrachAutoCustom()
	{
		return $this->belongsTo(Branch::class, 'branch_id', 'id');
	}

	public function getFirstFileDataCustom()
	{
		return $this->belongsTo(Files::class, 'bank_slip_id', 'id');
	}

	public function getSamraddhBankCustom()
	{
		return $this->belongsTo(SamraddhBank::class, 'head_office_bank_id','id');
	}
	
	public function getSamraddhBankCustomZeroMode()
	{
		return $this->belongsTo(SamraddhBank::class, 'to_bank_id','id');
	}

	public function samraddhBankCustom()
	{
		return $this->belongsTo(SamraddhBank::class, 'from_bank_id','id');
	}
	
	public function company() {
        return $this->belongsTo(Companies::class,'company_id');

    }
	public function company_branch() {
        return $this->belongsTo(CompanyBranch::class,'branch_id','branch_id');

    }



}
