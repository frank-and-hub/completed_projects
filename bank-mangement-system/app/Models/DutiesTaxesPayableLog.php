<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutiesTaxesPayableLog extends Model {
    protected $table = "duties_taxes_payable_logs";
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(Admin::class,'created_by_id','id');
    }
    public function branch()
    {
        return $this->belongsTo(User::class,'created_by_id','id');
    }
}
