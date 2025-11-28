<?php 
namespace App\Http\Controllers\Admin; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AllHeadTransaction;
use Session;
use Image;
use Redirect;
use Mail;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- CommanController
    |--------------------------------------------------------------------------
    |
    | This controller handles all functions which call multiple times .
*/
class HeadCommanController extends Controller
{
  /**
     * Create a new controller instance.
     * @return void
     */

  public function __construct()
    {
      // check user login or not
    //  $this->middleware('auth');
    }
 private static $commissionDistributeForMembers = '';
    private static $associateParent = ''; 
    private static $associateParentInvestment = '';
    private static $commissionDistributeForInvestment = ''; 
    /**
     *  create Payment transaction (only payment mode cash)
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function headTransactionCreate($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$head_id,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id)
    { 
      
        $data['daybook_ref_id']=$daybook_ref_id;
        $data['branch_id']=$branch_id;
        $data['bank_id']=$bank_id;
        $data['bank_ac_id']=$bank_ac_id;
        $data['head_id']=$head_id; 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['associate_id']=$associate_id;
        $data['member_id']=$member_id;
        $data['branch_id_to']=$branch_id_to;
        $data['branch_id_from']=$branch_id_from;
        $data['opening_balance']=$opening_balance;
        $data['amount']=$amount;
        $data['closing_balance']=$closing_balance;
        $data['description']=$description;
        $data['payment_type']=$payment_type;
        $data['payment_mode']=$payment_mode;
        $data['currency_code']=$currency_code;
        $data['amount_to_id']=$amount_to_id;
        $data['amount_to_name']=$amount_to_name;
        $data['amount_from_id']=$amount_from_id;
        $data['amount_from_name']=$amount_from_name;
        $data['jv_unique_id']=$jv_unique_id;
        $data['v_no']=$v_no;
        $data['v_date']=$v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id; 
        $data['cheque_no']=$cheque_no;
        $data['cheque_date']=$cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;


        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;

        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from']=$transction_bank_ifsc_from;
        $data['transction_bank_branch_from']=$transction_bank_branch_from;
        $data['transction_bank_from_id']=$transction_bank_from_id;
        $data['transction_bank_from_ac_id']=$transction_bank_from_ac_id; 
        $data['transction_bank_to']=$transction_bank_to;
        $data['transction_bank_ac_to']=$transction_bank_ac_to;
        $data['transction_bank_to_name']=$transction_bank_to_name;
        $data['transction_bank_to_ac_no']=$transction_bank_to_ac_no;
        $data['transction_bank_to_branch']=$transction_bank_to_branch;
        $data['transction_bank_to_ifsc']=$transction_bank_to_ifsc; 
        $data['transction_date']=$transction_date;
        $data['entry_date']=$entry_date;
        $data['entry_time']=$entry_time;
        $data['created_by']=$created_by;
        $data['created_by_id']=$created_by_id;
        $data['created_at']=$created_at;
        $data['updated_at']=$updated_at;
        $transcation = AllHeadTransaction::create($data);
        return true;
    }
}
