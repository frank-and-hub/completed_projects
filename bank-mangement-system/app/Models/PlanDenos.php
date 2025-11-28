<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Plans;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanDenos extends Model
{
    use SoftDeletes;
    protected $table = "plan_denos";
    protected $primaryKey = "id";
    protected $dates = ['deleted_at'];


    //create relation with the Plan Table
    public function plans()
    {
        return $this->belongsTo(Plans::class,'plan_id','id');
    }
    public function planTenure()
    {
        return $this->belongsTo(PlanTenures::class,'plan_id','plan_id');
    }

}