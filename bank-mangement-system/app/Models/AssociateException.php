<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateException extends Model
{
    protected $table = "associate_exceptions";
	public $timestamps = true;
    protected $guarded = [];
	
	  public function seniorData()
    {
        return $this->belongsTo(Member::class,'associate_id','id');
    }

}
