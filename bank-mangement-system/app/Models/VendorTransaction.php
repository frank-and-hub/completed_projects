<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class VendorTransaction extends Model

{

    protected $table = "vendor_transaction";

    protected $guarded = [];
    public function branch_detail()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }

    public function bill_detail()
    {
        return $this->belongsTo(VendorBill::class,'vendor_id','vendor_id');
    }

    public function vendor_detail()
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function advance_transactions()
    {
        return $this->belongsTo(AdvancedTransaction::class,'type_transaction_id','type_transaction_id');
    }

    public function branches(){
        return $this->belongsTo(Branch::class,'branch_id');
    }

}