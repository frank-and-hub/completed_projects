<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchCurrentBalance extends Model
{
    protected $table = "branch_current_balance";
    protected $guarded = [];
    public function cashBranch() {
        return $this->hasMany(Branch::class,'id','branch_id');
    }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
}