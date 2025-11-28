<?php

namespace App\Models;
use App\Models\Plans;
use Illuminate\Database\Eloquent\Model;

class PlanTenures extends Model {
    protected $table = "plan_tenures";
    protected $guarded = [];

    /**
     * Relation Create with Plans table
     * 
     * primary key = id of plans table
     *
     * table Plans
     * */
    public function plans()
    {
        return $this->belongsTo(Plans::class,'plan_id','id');
    }

}
