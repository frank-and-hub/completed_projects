<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNominee extends Model
{
    protected $table = "member_nominees";
    protected $guarded = [];

    public function nomineeRelationDetails() {
        return $this->belongsTo(Relations::class,'relation');
    }
    
}
