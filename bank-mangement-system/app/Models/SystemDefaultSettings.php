<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemDefaultSettings extends Model
{
    protected $table = "system_default_settings";

    protected $guarded = [];
	protected $casts=[
		'status'=>'string'
	];
	
	public function company() {
        return $this->hasMany(Companies::class,'id');
    }
	public function accountheads() {
        return $this->belongsTo(AccountHeads::class);
    }
	public function admin(){
        return $this->hasMany(Admin::class ,'id','created_by');
    }
}
