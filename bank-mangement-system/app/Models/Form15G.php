<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class Form15G extends Model

{

    protected $table = "form_g";

    protected $guarded = [];


    public function company(){
        return $this->belongsTo(Companies::class);
    }
	public function memberCompany() {
        return $this->belongsTo(MemberCompany::class,'member_id','id');
    }
	public function member() {
        return $this->belongsTo(Member::class,'customer_id','id');
    }

}