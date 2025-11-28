<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrsSavedData extends Model
{
	protected $table = "brs_saved_data";
	protected $fillable = ['bank_id','account_id','year','month','opening_balance','closing_balance','created_by','created_by_id','created_at','updated_at'];



	// public function memberDetail() {
    //     return $this->belongsTo(Member::class,'type_id','id');
    // }

}
