<?php 
namespace App\Http\Controllers\Branch; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator; 

use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\Transcation;
use App\Models\TranscationLog;
use App\Models\Investmentplantransactions;
use App\Models\AssociateCommission;
use App\Models\AssociateKotaBusiness;
use App\Models\AssociateKotaBusinessTeam;
use App\Models\Member;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use Carbon\Carbon;

use Session;
use Image;
use Redirect;
use URL;
use DB;
/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class TestController  extends Controller
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

    public function updateBalance()
    {
        $allInvestments = Memberinvestments::select('id','account_number','deposite_amount','due_amount','current_balance')->where('plan_id', '!=' , 1)/*->where('id',2616)*/->get();
        $wEntries = array();
        foreach ($allInvestments as $key => $investment) {
            $dayBooks = Daybook::select('id','investment_id','opening_balance','deposit')->where('transaction_type', '!=' , 1)->where('investment_id',$investment->id)->orderBy('transaction_type', 'ASC')->get();
            $dbCount = count($dayBooks);
            $pos = strstr($investment->account_number, 'R-');

            if($dbCount > 0){

                if($pos != ''){
                    foreach ($dayBooks as $dkey => $dayBook) {

                        if($dkey == 0){
                            if($dayBook->deposit != $dayBook->opening_balance){
                                $wEntries[] = $investment->id;
                                //break;
                            }else{
                                if($dkey != 0){
                                    $lKey = $dkey-1;
                                    $sAmount = $dayBook->deposit+$dayBooks[$lKey]->opening_balance;
                                    if($dayBook->opening_balance != $sAmount){
                                        $wEntries[] = $dayBook->investment_id;
                                        break;
                                    }
                                }
                            }
                        }          
                    }
                }else{
                    if($dayBooks[0]->deposit != $investment->deposite_amount || $dayBooks[0]->opening_balance != $investment->deposite_amount){
                        $wEntries[] = $investment->id;
                        //break;
                    }else{
                        foreach ($dayBooks as $dkey => $dayBook) {
                            if($dkey != 0){
                                $lKey = $dkey-1;
                                $sAmount = $dayBook->deposit+$dayBooks[$lKey]->opening_balance;
                                if($dayBook->opening_balance != $sAmount){
                                    $wEntries[] = $dayBook->investment_id;
                                    break;
                                }
                            }
                        }    
                    }
                }    
            }
        }
        echo count($wEntries);
        echo "<pre>"; print_r($wEntries); die;
    }

    public function updateDescription()
    {
        $dayBooks = Daybook::select('id')->where('name', 'like', '%R-%')->where('name', 'like', '%Collection%')->groupBy('browser')->get();
        $wEntries = array();
        foreach ($dayBooks as $key => $dayBook) {
            $dBook = SavingAccount::find($dayBook->id);
            $data['description'] = 'Opening  Balance';
            $dBook->update($data);
        }
    }

    public function updatedateform()
    {
      $data['title'] = "Update investment date";
      return view('templates.branch.investment_management.updatedate', $data);
    }

    public function updatedate(Request $request)
    {
        $lastdate = $request->lastdate;
        $newdate = $request->newdate;

        //$aCommissions = AssociateCommission::whereDate('created_at', '=', $lastdate)->get();

        $aCommissions = AssociateCommission::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate]);

        $aCommissionskota = AssociateKotaBusiness::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate]);

        $aCommissionskotaBusiness = AssociateKotaBusinessTeam::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate]);

        $member = Member::whereDate('created_at', '=', $lastdate)->update(['re_date' => $newdate,'associate_join_date' => $newdate,'created_at' => $newdate,'updated_at'=>$newdate]);

        $sAccount = SavingAccount::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate]);

        $sAccountTransaction = SavingAccountTranscation::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate]);

        $transaction = Transcation::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate,'payment_date'=>$newdate]);

        $transactionLog = TranscationLog::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate,'payment_date'=>$newdate]);

        $dbook = Daybook::whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate,'updated_at'=>$newdate,'payment_date'=>$newdate]);

        return back()->with('success', 'Date change Successfully!');
    
        /*$investments = Memberinvestments::select('id','account_number')->where('created_at', 'like', '%' . $lastdate . '%')->get();

        if ($investments) {
            foreach ($investments as $key => $investment) {
                $investId = $investment->id;
                $acc_n = $investment->account_number;
                DB::beginTransaction();
                try {
                      $updateInvestment = Memberinvestments::where('id',$investId)->update(['created_at' => $newdate]);

                      $updateDayBook = Daybook::where('account_no',$acc_n)->update(['created_at' => $newdate]);

                      $updateT = Transcation::where('transaction_type_id',$investId)->update(['created_at' => $newdate]);

                      $investIdTL = TranscationLog::where('transaction_type_id',$investId)->update(['created_at' => $newdate]);
                      
                      $time=strtotime($newdate);
                      $month=date("m",$time);

                      $updateIPT = Investmentplantransactions::where('investment_id',$investId)->update(['deposite_date' => $newdate,'created_at' => $newdate,'deposite_month'=>$month]);
                DB::commit();
                } catch (\Exception $ex) {
                    DB::rollback(); 
                    return back()->with('alert', $ex->getMessage());
                }
            }
            return back()->with('success', 'Saved Successfully!');
        }else {
          return back()->with('alert', 'Record not found!');
        }*/
    }
 public function insert_file_charge_in_cash_head()
    {
        $branchId = 5;
        $loanData = AllHeadTransaction::where('branch_id',$branchId)->where('type',5)->whereIn('sub_type',[57,58])->get();
        
        foreach($loanData as $value)
        {
            if($value->sub_type == 57)
            {
               
                $checkFileChargeType =Memberloans::where('id',$value->type_id)->where('file_charge_type',0)->first();
                
                if($checkFileChargeType)
                {
                    $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] =$value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['head_id'] = 28;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;  ;
                    $record['associate_id'] =$value->associate_id;
                    $record['member_id'] =$value->member_id ;
                    $record['branch_id_to'] =$value->branch_id_to ;
                    $record['branch_id_from'] =$value->branch_id_from ;
                    $record['opening_balance'] =$value->amount ;
                    $record['amount'] =$value->amount ;
                    $record['closing_balance'] =$value->amount ;
                    $record['description'] =$value->description;
                    $record['payment_type'] ='DR' ;
                    $record['payment_mode'] =$value->payment_mode ;
                    $record['currency_code'] =$value->currency_code ;
                    $record['amount_to_id'] =$value->amount_to_id;
                    $record['amount_to_name'] =$value->amount_to_name ;
                    $record['amount_from_id'] =$value->amount_from_id; ;
                    $record['amount_from_name'] =$value->amount_from_name; ;
                    $record['v_no'] =$value->v_no;
                    $record['v_date'] =$value->v_date; ;
                    $record['ssb_account_id_from'] =$value->ssb_account_id_from ;
                    $record['ssb_account_id_to'] =$value->ssb_account_id_to ;
                    $record['cheque_no'] =null;
                    $record['cheque_date'] =null;
                    $record['cheque_bank_from'] =null ;
                    $record['cheque_bank_ac_from'] =null ;
                    $record['cheque_bank_ifsc_from'] =null ;
                    $record['cheque_bank_branch_from'] =null ;
                    $record['cheque_bank_from_id'] =null ;
                    $record['cheque_bank_ac_from_id'] =null;
                    $record['cheque_bank_to'] =null ;
                    $record['cheque_bank_ac_to'] =null;
                    $record['cheque_bank_to_name'] =null ;
                    $record['cheque_bank_to_branch'] = null;
                    $record['cheque_bank_to_ac_no'] = null;
                    $record['cheque_bank_to_ifsc'] =null ;
                    $record['transction_no'] =null ;
                    $record['transction_bank_from'] =null ;
                    $record['transction_bank_ac_from'] =null;
                    $record['transction_bank_ifsc_from'] =null ;
                    $record['transction_bank_branch_from'] =null ;
                    $record['transction_bank_from_id'] =null ;
                    $record['transction_bank_from_ac_id'] =null;
                    $record['transction_bank_to'] =null ;
                    $record['transction_bank_ac_to'] =null ;
                    $record['transction_bank_to_name'] =null ;
                    $record['transction_bank_to_ac_no'] =null ;
                    $record['transction_bank_to_branch'] =null;
                    $record['transction_bank_to_ifsc'] =null;
                    $record['transction_date'] =$value->transaction_date;
                    $record['entry_date'] =$value->entry_date;;
                    $record['entry_time'] =$value->entry_time ;
                    $record['created_by'] =$value->created_by ;
                    $record['created_by_id'] =$value->created_by_id ;
                    $record['created_at'] =$value->created_at ;
                    $record['updated_at'] =$value->updated_at ;
                    $record['ssb_account_tran_id_to'] =null;
                    $record['ssb_account_tran_id_from'] =null;
                     $insertData = AllHeadTransaction::create($record);
                     print_r("success");
                }
            }
            else{
                 $checkFileChargeType =Grouploans::where('id',$value->type_id)->where('file_charge_type',0)->first();
                if($checkFileChargeType)
                {
                    $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] =$value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['head_id'] = 28;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;  ;
                    $record['associate_id'] =$value->associate_id;
                    $record['member_id'] =$value->member_id ;
                    $record['branch_id_to'] =$value->branch_id_to ;
                    $record['branch_id_from'] =$value->branch_id_from ;
                    $record['opening_balance'] =$value->amount ;
                    $record['amount'] =$value->amount ;
                    $record['closing_balance'] =$value->amount ;
                    $record['description'] =$value->description;
                    $record['payment_type'] ='DR' ;
                    $record['payment_mode'] =$value->payment_mode ;
                    $record['currency_code'] =$value->currency_code ;
                    $record['amount_to_id'] =$value->amount_to_id;
                    $record['amount_to_name'] =$value->amount_to_name ;
                    $record['amount_from_id'] =$value->amount_from_id; ;
                    $record['amount_from_name'] =$value->amount_from_name; ;
                    $record['v_no'] =$value->v_no;
                    $record['v_date'] =$value->v_date; ;
                    $record['ssb_account_id_from'] =$value->ssb_account_id_from ;
                    $record['ssb_account_id_to'] =$value->ssb_account_id_to ;
                    $record['cheque_no'] =null;
                    $record['cheque_date'] =null;
                    $record['cheque_bank_from'] =null ;
                    $record['cheque_bank_ac_from'] =null ;
                    $record['cheque_bank_ifsc_from'] =null ;
                    $record['cheque_bank_branch_from'] =null ;
                    $record['cheque_bank_from_id'] =null ;
                    $record['cheque_bank_ac_from_id'] =null;
                    $record['cheque_bank_to'] =null ;
                    $record['cheque_bank_ac_to'] =null;
                    $record['cheque_bank_to_name'] =null ;
                    $record['cheque_bank_to_branch'] = null;
                    $record['cheque_bank_to_ac_no'] = null;
                    $record['cheque_bank_to_ifsc'] =null ;
                    $record['transction_no'] =null ;
                    $record['transction_bank_from'] =null ;
                    $record['transction_bank_ac_from'] =null;
                    $record['transction_bank_ifsc_from'] =null ;
                    $record['transction_bank_branch_from'] =null ;
                    $record['transction_bank_from_id'] =null ;
                    $record['transction_bank_from_ac_id'] =null;
                    $record['transction_bank_to'] =null ;
                    $record['transction_bank_ac_to'] =null ;
                    $record['transction_bank_to_name'] =null ;
                    $record['transction_bank_to_ac_no'] =null ;
                    $record['transction_bank_to_branch'] =null;
                    $record['transction_bank_to_ifsc'] =null;
                    $record['transction_date'] =$value->transaction_date;
                    $record['entry_date'] =$value->entry_date;;
                    $record['entry_time'] =$value->entry_time ;
                    $record['created_by'] =$value->created_by ;
                    $record['created_by_id'] =$value->created_by_id ;
                    $record['created_at'] =$value->created_at ;
                    $record['updated_at'] =$value->updated_at ;
                    $record['ssb_account_tran_id_to'] =null;
                    $record['ssb_account_tran_id_from'] =null;
                     $insertData = AllHeadTransaction::create($record);
                     print_r("success");
                }
            }
         
        }
           
    }
    
    
    public function update_loan_amount()
    {
        $branch_id = 16;
        $records = AllHeadTransaction::where('branch_id',$branch_id)->where('type',5)->where('sub_type',57)->where('head_id',90)->get();
        foreach($records as $data)
        {
            $existRecord = Memberloans::where('id',$data->type_id)->first();
            $loanAmount = AllHeadTransaction::where('type',5)->where('sub_type',51)->whereIn('head_id',[64,65,67])->where('type_id',$data->type_id)->first();
            if($existRecord->amount != $loanAmount->amount)
            {
                
                $loanAmount->update(['amount'=>$existRecord->amount,'opening_balance'=>$existRecord->amount,'closing_balance'=>$existRecord->amount]);;
                print_r('success');
            }
            $branchData = BranchDaybook::where('daybook_ref_id',$loanAmount->daybook_ref_id)->where('type',5)->where('sub_type',51)->first();
                $branchData->update(['amount'=>$existRecord->amount,'opening_balance'=>$existRecord->amount,'closing_balance'=>$existRecord->amount,'description_dr'=>'To'.$existRecord->account_number.'A/C Dr ' .$existRecord->amount]);
        }
    }
    
    public function update_grploan_amount()
    {
       $branch_id = 16;
        $records = AllHeadTransaction::whereIN('branch_id',[$branch_id])->where('type',5)->where('sub_type',58)->where('head_id',90)->get();
       
        foreach($records as $data)
        {
            $existRecord = Grouploans::where('id',$data->type_id)->first();
            $loanAmount = AllHeadTransaction::where('type',5)->where('sub_type',54)->whereIn('head_id',[66])->where('type_id',$data->type_id)->first();
            if(isset($existRecord->amount))
            {   
                  if($loanAmount=='')
                   {
                       dd($data->id);
                   }
                     if($existRecord->amount != $loanAmount->amount)
                {
                 
                    $loanAmount->update(['amount'=>$existRecord->amount,'opening_balance'=>$existRecord->amount,'closing_balance'=>$existRecord->amount]);;
                    print_r('success');
                }
                 $branchData = BranchDaybook::where('daybook_ref_id',$loanAmount->daybook_ref_id)->where('type',5)->where('sub_type',54)->first();
                  $branchData->update(['amount'=>$existRecord->amount,'opening_balance'=>$existRecord->amount,'closing_balance'=>$existRecord->amount,'description_dr'=>'To'.$existRecord->account_number.'A/C Dr ' .$existRecord->amount]);
                  print_r('success');
            }
            else{
                dd($data->type_id);
            }
           
        }
    }

    public function insert_mb_interest_in_deposite()
    {
        $branch_id = 15;
        $records = AllHeadTransaction::where('branch_id',$branch_id)->where('type',3)->where('sub_type',36)->where('head_id',36)->get();
        foreach ($records as $key => $value) {
            $ifexists = AllHeadTransaction::where('head_id',85)->where('daybook_ref_id',$value->daybook_ref_id)->count();
            //dd($ifexists);
            if($ifexists == 0)
            {
               //dd($value->id);
                 $record['daybook_ref_id'] = $value->daybook_ref_id;
                    $record['branch_id'] = $value->branch_id;
                    $record['type'] =$value->type;
                    $record['sub_type'] = $value->sub_type;
                    $record['head_id'] = 85;
                    $record['type_id'] = $value->type_id;
                    $record['type_transaction_id'] = $value->type_transaction_id;  ;
                    $record['associate_id'] =$value->associate_id;
                    $record['member_id'] =$value->member_id ;
                    $record['branch_id_to'] =$value->branch_id_to ;
                    $record['branch_id_from'] =$value->branch_id_from ;
                    $record['opening_balance'] =$value->amount ;
                    $record['amount'] =$value->amount ;
                    $record['closing_balance'] =$value->amount ;
                    $record['description'] =$value->description;
                    $record['payment_type'] ='CR' ;
                    $record['payment_mode'] =$value->payment_mode ;
                    $record['currency_code'] =$value->currency_code ;
                    $record['amount_to_id'] =$value->amount_to_id;
                    $record['amount_to_name'] =$value->amount_to_name ;
                    $record['amount_from_id'] =$value->amount_from_id; ;
                    $record['amount_from_name'] =$value->amount_from_name; ;
                    $record['v_no'] =$value->v_no;
                    $record['v_date'] =$value->v_date; ;
                    $record['ssb_account_id_from'] =$value->ssb_account_id_from ;
                    $record['ssb_account_id_to'] =$value->ssb_account_id_to ;
                    $record['cheque_no'] =null;
                    $record['cheque_date'] =null;
                    $record['cheque_bank_from'] =null ;
                    $record['cheque_bank_ac_from'] =null ;
                    $record['cheque_bank_ifsc_from'] =null ;
                    $record['cheque_bank_branch_from'] =null ;
                    $record['cheque_bank_from_id'] =null ;
                    $record['cheque_bank_ac_from_id'] =null;
                    $record['cheque_bank_to'] =null ;
                    $record['cheque_bank_ac_to'] =null;
                    $record['cheque_bank_to_name'] =null ;
                    $record['cheque_bank_to_branch'] = null;
                    $record['cheque_bank_to_ac_no'] = null;
                    $record['cheque_bank_to_ifsc'] =null ;
                    $record['transction_no'] =null ;
                    $record['transction_bank_from'] =null ;
                    $record['transction_bank_ac_from'] =null;
                    $record['transction_bank_ifsc_from'] =null ;
                    $record['transction_bank_branch_from'] =null ;
                    $record['transction_bank_from_id'] =null ;
                    $record['transction_bank_from_ac_id'] =null;
                    $record['transction_bank_to'] =null ;
                    $record['transction_bank_ac_to'] =null ;
                    $record['transction_bank_to_name'] =null ;
                    $record['transction_bank_to_ac_no'] =null ;
                    $record['transction_bank_to_branch'] =null;
                    $record['transction_bank_to_ifsc'] =null;
                    $record['transction_date'] =$value->transaction_date;
                    $record['entry_date'] =$value->entry_date;;
                    $record['entry_time'] =$value->entry_time ;
                    $record['created_by'] =$value->created_by ;
                    $record['created_by_id'] =$value->created_by_id ;
                    $record['created_at'] =$value->created_at ;
                    $record['updated_at'] =$value->updated_at ;
                    $record['ssb_account_tran_id_to'] =null;
                    $record['ssb_account_tran_id_from'] =null;
                     $insertData = AllHeadTransaction::create($record);
                     print_r("success");
            }
        }

    }

    public function update_stationary_payment_mode()
    {
        $branch_id=25;
        $records = AllHeadTransaction::where('head_id',122)->whereIn('type',[21])->where('sub_type',NULL)->where('branch_id',$branch_id)->get();
        foreach ($records as $key => $value) {
            $value->update(['payment_type'=>'CR']);
            $newRewcord = AllHeadTransaction::where('daybook_ref_id',$value->daybook_ref_id)->where('head_id',28)->update(['payment_type'=>'DR']);
            print_r('success');
        }
    }
}

