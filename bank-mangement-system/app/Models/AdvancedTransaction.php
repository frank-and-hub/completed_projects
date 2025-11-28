<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class AdvancedTransaction extends Model

{

    protected $table = "advanced_transaction";

    protected $guarded = [];
    public function branch(){
        return $this->belongsTo(Branch::class);
    }

    public function Employee(){
        return $this->belongsTo(Employee::class,'type_id','id');
    }

    public function rentLiability(){
        return $this->belongsTo(RentLiability::class,'type_id','id');
    }

    public function admin(){
        return $this->belongsTo(Admin::class,'created_by');
    }
    public function user(){
        return $this->belongsTo(Admin::class,'created_by_id');
    }
    public function branchUser(){
        return $this->belongsTo(User::class,'created_by_id');
    }
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }

    public static function getHeadId($type){
      
       switch($type)
       {
        case 0:
           $headId = 74;
        break;
        
        case 1:
            $headId = 73;
        break;
      default :
            $headId = 72;
       }
       return $headId ;
    }
}