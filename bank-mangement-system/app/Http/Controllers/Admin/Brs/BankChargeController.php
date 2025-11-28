<?php
namespace App\Http\Controllers\Admin\Brs;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBankDaybook ;
use App\Models\SamraddhBank; 
use App\Models\Companies; 
use App\Models\AccountHeads;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use App\Models\SamraddhBankAccount;
use App\Http\Controllers\Admin\CommanController;
class BankChargeController extends Controller
{


	public function __construct(){
		$this->middleware('auth');
	}

	// BankAccount  Report 
	

	public function bank_charge(){
		$data['title'] = "Bank Charge Management";
	    $data['banks'] = SamraddhBank::where('status',1)->get(['id','bank_name']);

	   

		return view('templates.admin.brs.bank_charge',$data);
	}
	public function chargeSave(Request $request)
    {
    	//print_r($_POST);
        DB::beginTransaction();
        try{
            $company_id = $request->company_id;
        	$globaldate=$request->created_at; 
        	
            $currency_code='INR';
            $select_date=$request->date;
            
            $current_date=date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date=date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time=date("H:i:s", strtotime(convertDate($globaldate))); 
            $date_create=$entry_date.' '.$entry_time;
            $created_by=1;
            $created_by_id=\Auth::user()->id;
            $created_by_name=\Auth::user()->username; 
            $created_at=date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($date_create))); 
            Session::put('created_at', $created_at); 


            $amount=$request->amount;           
            $type=18;
            $sub_type=181;
            $type_id=$request->bank_charge;
            $payment_type='DR';
            $payment_mode=4;
            $type_transaction_id = NULL;
            $bank_id=$request->bank;
            $bank_ac_id=$request->bank_account;         
            $bankDtail=getSamraddhBank($bank_id);
            $bankAcDetail=getSamraddhBankAccountId($bank_ac_id);
            $bankBla = \App\Models\BankBalance::where('bank_id',$bank_id)->where('account_id', $bank_ac_id)->where('company_id',$company_id)->whereDate('entry_date','<=',$entry_date)->orderby('entry_date','desc')->sum('totalAmount');           
            $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);
            $refId=$daybook_ref_id=$daybookRef;
            $member_id=NULL;          
            if($bankBla){
                if($amount > $bankBla)
                {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
            } 
            else
            {
                return back()->with('alert', 'Sufficient amount not available in bank account!');
            }
            $amount_from_id=$bank_id;
            $amount_from_name=$bankDtail->bank_name.'('.$bankAcDetail->account_no.')';
            $amount_to_id=$type_id;
            $amount_to_name=getAcountHeadNameHeadId($type_id);
          
            $opening_balance=NULL;$closing_balance=NULL; $v_no=NULL;$v_date=NULL;$ssb_account_id_from=NULL;$ssb_account_id_to=NULL; $cheque_no=NULL;$cheque_date= NULL;  $cheque_bank_from= NULL;  $cheque_bank_ac_from= NULL;  $cheque_bank_ifsc_from= NULL;  $cheque_bank_branch_from= NULL;  $cheque_bank_to= NULL;  $cheque_bank_ac_to= NULL;  $cheque_bank_from_id= NULL;  $cheque_bank_ac_from_id= NULL;  $cheque_bank_to_name= NULL;  $cheque_bank_to_branch= NULL;  $cheque_bank_to_ac_no= NULL;  $cheque_bank_to_ifsc= NULL;   $transction_no= NULL;  $transction_bank_from= NULL;  $transction_bank_ac_from= NULL;  $transction_bank_ifsc_from= NULL;  $transction_bank_branch_from= NULL;  $transction_bank_to= NULL;  $transction_bank_ac_to= NULL;  $transction_date= NULL;    $transction_bank_from_id= NULL;  $transction_bank_from_ac_id= NULL;  $transction_bank_to_name= NULL;  $transction_bank_to_ac_no= NULL;  $transction_bank_to_branch=NULL;$transction_bank_to_ifsc=NULL;
            $jv_unique_id=NULL;  $ssb_account_tran_id_to=NULL; $ssb_account_tran_id_from=NULL; $cheque_type=NULL; $cheque_id=NULL;   
            
            $description_dr='Bank A/c Dr '.$amount.'/-'; 
            $description_cr='To '.getAcountHeadNameHeadId($type_id).' A/c Cr '.$amount.'/-'; 
         
            $des = $request->description;
                ///  ------- bank charge head ---------------

          
               
                $allTranNeft = CommanController::newHeadTransactionCreate($daybook_ref_id,29,$bank_id,$bank_ac_id,$type_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$des,'DR',$payment_mode,$currency_code,$v_no,$ssb_account_id_from,$cheque_no,$transction_no,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$company_id);               
          

            $allTran2 = CommanController::newHeadTransactionCreate($daybook_ref_id,29,$bank_id,$bank_ac_id,$bankDtail->account_head_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$des,'CR',$payment_mode,$currency_code,$v_no,$ssb_account_id_from,$cheque_no,$transction_no,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$company_id);
           
            $smbdc=CommanController::createSamraddhBankDaybookModify($daybook_ref_id,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id=NULL,$member_id,$branch_id=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$company_id); 
         
        	DB::commit();
        }
        catch (\Exception $ex)         
        {
            DB::rollback();
           
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Bank Charge Created Successfully!'); 
    }

    public function companydate(Request $request)
    {
        $data['companyDate'] = Companies::where('id',$request['company_id'])->first('created_at')->created_at;
        $data['companyDate'] = date('d/m/Y', strtotime(convertDate($data['companyDate'])));
         return json_encode($data['companyDate']);
    }
   
}