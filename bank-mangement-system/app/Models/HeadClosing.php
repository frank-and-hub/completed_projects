<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadClosing extends Model {
    protected $table = "head_closing_balances";
    protected $guarded = [];

    public function accountHeads()
    {
        return $this->belongsTo(AccountHeads::class,'head_id','head_id');
    }
}
