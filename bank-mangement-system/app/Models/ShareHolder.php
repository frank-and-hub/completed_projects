<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class ShareHolder extends Model

{

    protected $table = "shareholders";

    protected $guarded = [];

     public function account_head()

    {

    	return $this->belongsTo('App\Models\AccountHeads', 'head_id','head_id');

    }

	

	

	public function member() {

        return $this->belongsTo(Member::class);

    }

	public function getMember() {

        return $this->hasMany(Member::class,'id','member_id');

    }
    public function company() {

        return $this->belongsTo(Companies::class,'company_id','id');

    }
    
   public function membercompany(){
    return $this->hasOne(MemberCompany::class, 'customer_id', 'id');
    }

   public function memberDetail(){
    return $this->belongsTo(MemberCompany::class, 'customer_id', 'id');
   }

}