<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model {
    protected $table = "expenses";
    protected $guarded = [];
	 public function branch() {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function head() {
        return $this->belongsTo(AccountHeads::class,'account_head_id','head_id');
    }
    public function subb_head() {
        return $this->belongsTo(AccountHeads::class,'sub_head1','head_id');
    }
    public function subb_head2() {
        return $this->belongsTo(AccountHeads::class,'sub_head2','head_id');
    }
}
