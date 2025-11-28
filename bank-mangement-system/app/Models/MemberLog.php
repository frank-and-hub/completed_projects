<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Admin,User};

class MemberLog extends Model
{
    protected $table = "member_logs";
    protected $guarded = [];

    /**
     * Apply getter (accessor) to convert date format
     * @param dob
     * @return string
     */
    public function member_data()
    {
        return $this->belongsTo(Member::class,'customer_id');
    }

    //fetching the data from the admin table
    public function adminData()
    {
        return $this->belongsTo(Admin::class,'created_by_id','id');
    }

    //fetching the date from the users table
    public function userData()
    {
        return $this->belongsTo(User::class,'created_by_id','id');
    }
    
}
