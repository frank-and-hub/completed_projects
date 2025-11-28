<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubAccountHeads extends Model {
    protected $table = "sub_account_heads";
    protected $guarded = [];

    public function accountHead()
    {
        return $this->hasOne('App\Models\AccountHeads', 'id','account_head_id');
    }
}
