<?php 
namespace App\Http\Controllers\Admin; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\AllHeadTransaction;  
use App\Models\AllTransaction;  
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Account Management AccountImplementController
    |--------------------------------------------------------------------------
    |
    | This controller handles Account all functionlity.
*/
class AccountDataTransferController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }     

  //   public function changeDrCr()
  //   {
  //     echo 'hi';die;
	 //  /* first run query on db  -- 
	  
		// 	ALTER TABLE `all_transaction` CHANGE `payment_type` `payment_type` ENUM('DR','CR','DR1','CR1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
			
	 //  */
  //       //$head2DR = AllTransaction::where('head1',2)->where('payment_type','DR')->update(['payment_type' => 'CR1' ]);
		// //$head2CR = AllTransaction::where('head1',2)->where('payment_type','CR')->update(['payment_type' => 'DR1' ]);

  //       //$head4DR = AllTransaction::where('head1',4)->where('payment_type','DR')->update(['payment_type' => 'CR1' ]);
		// //$head4CR = AllTransaction::where('head1',4)->where('payment_type','CR')->update(['payment_type' => 'DR1' ]); 
		
		// $headCR1 = AllTransaction::where('payment_type','CR1')->update(['payment_type' => 'CR' ]); 
		// $headDR1 = AllTransaction::where('payment_type','DR1')->update(['payment_type' => 'DR' ]);
		// echo 'done1';die;  
		
		// /*All query run than run last query in database table -- 
		
		// 	ALTER TABLE `all_transaction` CHANGE `payment_type` `payment_type` ENUM('DR','CR') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
			
	 //  */
        
  //   }

    public function dataMoveAllHeadTransaction()
    {
		// echo 'hi-- 0 to 10000';die;
        $getdata = AllTransaction::where('id','>', 0)->where('id','<=', 10000)->get(); 
        foreach($getdata as $val) 
        {

            DB::beginTransaction();
            try 
            { 
                $head=0;

                if($val->head1>0)
                {
                    $head = $val->head1;
                }
                if($val->head2>0)
                {
                    $head = $val->head2;
                }
                if($val->head3>0)
                {
                    $head = $val->head3;
                }
                if($val->head4>0 && $val->head4!=71 )
                {
                    $head = $val->head4;
                }
                if($val->head5>0)
                {
                    $head = $val->head5;
                }
                        $allTran['daybook_ref_id'] = $val->daybook_ref_id;
                        $allTran['branch_id'] = $val->branch_id;
                        $allTran['bank_id'] = $val->branch_id;
                        $allTran['bank_ac_id'] = $val->bank_ac_id;
                        $allTran['head_id'] = $head;  
                        $allTran['type'] =$val->type;
                        $allTran['sub_type'] = $val->sub_type;
                        $allTran['type_id'] = $val->type_id; 
                        $allTran['type_transaction_id'] = $val->type_transaction_id;
                        $allTran['associate_id'] = $val->associate_id;
                        $allTran['member_id'] = $val->member_id;
                        $allTran['branch_id_to'] = $val->branch_id_to;
                        $allTran['branch_id_from'] = $val->branch_id_from;
                        $allTran['opening_balance'] = $val->opening_balance;
                        $allTran['amount'] = $val->amount;
                        $allTran['closing_balance'] =$val->closing_balance;
                        $allTran['description'] = $val->description;
                        $allTran['payment_type'] = $val->payment_type;
                        $allTran['payment_mode'] = $val->payment_mode;
                        $allTran['currency_code'] =$val->currency_code;
                        $allTran['amount_to_id'] = $val->amount_to_id;
                        $allTran['amount_to_name'] = $val->amount_to_name;
                        $allTran['amount_from_id'] = $val->amount_from_id;
                        $allTran['amount_from_name'] = $val->amount_from_name; 
                        $allTran['v_no'] = $val->v_no;                       
                        $allTran['v_date'] = $val->v_date;
                        $allTran['ssb_account_id_from'] = $val->ssb_account_id_from;
                        $allTran['ssb_account_id_to'] = $val->ssb_account_id_to;
                        $allTran['ssb_account_tran_id_to'] = $val->ssb_account_tran_id_to;
                        $allTran['ssb_account_tran_id_from'] = $val->ssb_account_tran_id_from; 
                        $allTran['cheque_no'] = $val->cheque_no;
                        $allTran['cheque_date'] = $val->cheque_date;
                        $allTran['cheque_bank_from'] = $val->cheque_bank_from;
                        $allTran['cheque_bank_ac_from'] = $val->cheque_bank_ac_from;
                        $allTran['cheque_bank_ifsc_from'] = $val->cheque_bank_ifsc_from;                        
                        $allTran['cheque_bank_branch_from'] = $val->cheque_bank_branch_from;
                        $allTran['cheque_bank_from_id'] =$val->cheque_bank_from_id;
                        $allTran['cheque_bank_ac_to'] = $val->cheque_bank_ac_to;
                        $allTran['cheque_bank_to_name'] = $val->cheque_bank_to_name;
                        $allTran['cheque_bank_to_branch'] = $val->cheque_bank_to_branch;                        
                        $allTran['cheque_bank_to_ac_no'] = $val->cheque_bank_to_ac_no;
                        $allTran['cheque_bank_to_ifsc'] = $val->cheque_bank_to_ifsc;
                        $allTran['transction_no'] = $val->transction_no;
                        $allTran['transction_bank_from'] = $val->transction_bank_from;
                        $allTran['transction_bank_ac_from'] = $val->transction_bank_ac_from;                        
                        $allTran['transction_bank_ifsc_from'] = $val->transction_bank_ifsc_from;
                        $allTran['transction_bank_branch_from'] = $val->transction_bank_branch_from;
                        $allTran['transction_bank_from_id'] = $val->transction_bank_from_id;
                        $allTran['transction_bank_from_ac_id'] = $val->transction_bank_from_ac_id;
                        $allTran['transction_bank_to'] = $val->transction_bank_to;                        
                        $allTran['transction_bank_ac_to'] = $val->transction_bank_ac_to; 
                        $allTran['transction_bank_to_name'] = $val->transction_bank_to_name; 
                        $allTran['transction_bank_to_ac_no'] = $val->transction_bank_to_ac_no; 
                        $allTran['transction_bank_to_branch'] = $val->transction_bank_to_branch; 
                        $allTran['transction_bank_to_ifsc'] =$val->transction_bank_to_ifsc; 
                        $allTran['transction_date'] = $val->transction_date; 
                        $allTran['entry_date'] = $val->entry_date; 
                        $allTran['entry_time'] = $val->entry_time; 
                        $allTran['created_by'] = $val->created_by; 
                        $allTran['created_by_id'] =$val->created_by_id; 
                        $allTran['created_at'] = $val->created_at; 
                        $allTran['updated_at'] = $val->updated_at;  

					$allTranSave = AllHeadTransaction::create($allTran);
					$allTranSaveID = $allTranSave->id;
                DB::commit();
            } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
            }    

        }
 
        echo 'done';die;
    }

}
