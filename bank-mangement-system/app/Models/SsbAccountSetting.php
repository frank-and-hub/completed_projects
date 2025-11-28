<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsbAccountSetting extends Model {
    protected $table = "ssb_account_setting";
	protected $fillable = [
        'user_type','plan_type', 'amount', 
    ];
    protected $guarded = [];
}
