<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceSheetClosing extends Model {
    protected $table = "bs_closing";
    protected $guarded = [];

    public function accountHeads()
    {
        return $this->belongsTo(AccountHeads::class,'head_id','head_id');
    }

    //    public function closedBalanceSheet()
    // {
    //     return $this->belongsTo(BalanceSheetClosed::class,['branch_id','start_year','end_year']);
    // }
}
