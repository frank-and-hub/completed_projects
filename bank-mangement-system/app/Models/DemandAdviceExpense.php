<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class DemandAdviceExpense extends Model

{

    protected $table = "demand_advices_fresh_expenses";

    protected $guarded = [];  

    public function advices() {

        return $this->belongsTo(DemandAdvice::class,'demand_advice_id');

    }

    public function AcountHeadNameHeadIdCustom() {

        return $this->belongsTo(AccountHeads::class,'assets_category','head_id');

    }

    public function AssestFilesCustom() {

        return $this->belongsTo(Files::class,'bill_file_id');

    }

    public function AcountHeadNameHeadIdCustom2() {

        return $this->belongsTo(AccountHeads::class,'assets_subcategory','head_id');

    }
    /**
     * below 2 models updated on 09-oct-2023
     * by shahid
     */
    public function company(){
        return $this->belongsTo(Companies::class,'company_id');
    }
    public function VendorBil(){
        return $this->belongsTo(VendorBill::class,'billId');
    }


}

 