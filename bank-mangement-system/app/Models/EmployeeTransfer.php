<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $table = "employee_transfer";
    protected $guarded = [];  

    public function transferEmployee() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function transferBranch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }
    public function transferBranchOld() {
        return $this->belongsTo(Branch::class,'old_branch_id');
    }

    public function designation() {
        return $this->belongsTo(Designation::class,'designation_id');
    } 

    public function oldDesignation() {
        return $this->belongsTo(Designation::class,'old_designation_id');
    }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }

}