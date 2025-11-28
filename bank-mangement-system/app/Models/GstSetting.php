<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ActiveScope;

class GstSetting extends Model
{
    use SoftDeletes;
    protected $table = "gst_setting";
    protected $guarded = [];
    protected $dates = ['deleted_at'];


    public function State()
    {
        return $this->belongsTo(State::class,'state_id','id');
    }

    // public static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new CompanyScope);
    // }
    public function company() {
        return $this->belongsTo(Companies::class,'company_id');
    }
}