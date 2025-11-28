<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadSetting extends Model
{
    protected $table="head_setting";
    protected $guarded = [];
    
    public function HeadDetail()
    {
        return $this->belongsTo(AccountHeads::class,'head_id','head_id');
    }
}
