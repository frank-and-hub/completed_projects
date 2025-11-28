<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loanscoapplicantdetails extends Model
{
    protected $table = "loans_coapplicant_details";
    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
}
