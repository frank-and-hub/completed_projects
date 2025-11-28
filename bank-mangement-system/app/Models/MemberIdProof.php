<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberIdProof extends Model
{
    protected $table = "member_id_proofs";
    protected $guarded = [];

    public function idTypeFirst()
    {
        return $this->belongsTo(IdType::class,'first_id_type_id','id');
    }
     public function idTypeSecond()
    {
        return $this->belongsTo(IdType::class,'second_id_type_id','id');
    }
    public function member()
    {
        return $this->belongsTo(Member::class,'member_id','id');
    }
}
