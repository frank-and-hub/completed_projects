<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivedVoucher extends Model
{
    protected $table = "received_vouchers";
    protected $guarded = [];   

    public function rv_employee() {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    
    public function rv_branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function rvCheque() {
        return $this->belongsTo(ReceivedCheque::class,'cheque_id');
    }
    
    public function receivedCompany() {
        return $this->belongsTo(Companies::class,'company_id');

    }
    public function company()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }
    public function rv_member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}