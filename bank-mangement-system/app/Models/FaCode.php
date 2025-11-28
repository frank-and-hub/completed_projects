<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class FaCode extends Model
{
    protected $table = "fa_codes";
    public function company(){
		return $this->belongsTo(Companies::class,'id','company_id');
	}
}
