<?php

namespace App\Models;
use Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
class PlanCategory extends Model {
    protected $table = "plan_categories";
    protected $primaryKey = "id";
    protected $guarded = [];
    protected $casts = [
        "content" => "array",
    ];
    public function setDobAttribute($value)
    {
        dd($value);

        $this->attributes['dob'] = Crypt::encrypt($value);
    }

    /**
    * Generate Relation with ADMIN table
    * Table Admin
    * @return \Illuminate\Http\Response
    */
    public function admin()
    {
        return $this->hasOne(Admin::class,'id','created_by');
    }

    /**
    * Generate Relation with Admin table with created_by_id of plan_categories table
    * Table admin
    * @return \Illuminate\Http\Response
    */
    public function admins()
    {
        return $this->hasOne(Admin::class,'id','created_by_id');
    }

    /**
    * Generate Relation with account_heads table
    * Table account_heads
    * @return \Illuminate\Http\Response
    */
    public function accountheads()
    {
        return $this->hasOne(AccountHeads::class,'head_id','head_id');
    }
}
