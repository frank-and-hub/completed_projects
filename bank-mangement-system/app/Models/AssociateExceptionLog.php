<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateExceptionLog extends Model
{
    protected $table = "associate_exception_logs";
	public $timestamps = true;
    protected $guarded = [];
	
	  public function seniorData()
    {
        return $this->belongsTo(Member::class,'associate_id','id');
    }

}
