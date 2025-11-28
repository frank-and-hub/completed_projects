<?php

namespace App\Http\Controllers\Api;



use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Admin\CommanController;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;

use Validator;

use App\Models\Member;

use App\Models\Memberloans;

use App\Models\Grouploans;

use App\Models\SavingAccountTranscation;

use App\Models\SavingAccount;
use App\Models\LoanDayBooks;
use App\Models\TransactionReferences;
use App\Models\Daybook;
use Carbon\Carbon;
use Session;
use DB;


class AssociateLoanController extends Controller

{   



    /**

     * Fetch member investments.

     *

     * @param  \App\Request  $request

     * @return \Illuminate\Http\Response

     */

    function getCategoryTree($parent_id,  $tree_array = array()) {

        $categories = associateTreeid($parent_id);

        

        foreach ($categories as $item){ 

          $tree_array[] =   ['member_id'=>$item->member_id,'status'=>$item['member']->associate_status,'is_block'=>$item['member']->is_block];

          $tree_array = $this->getCategoryTree($item->member_id, $tree_array);

            

            

        }

        return $tree_array;

    }





    public function loanList(Request $request)

    {   

        $associate_no = $request->associate_no;  

        

        try { 



        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();



        	if ($member) {

                $token = md5($request->associate_no);

                if($token == $request->token){

                    if($request->page>0 && $request->length>0)

                    {

                        $data = array();



                        



                    $loan_data = Memberloans::with('loan','loanMember','loanMemberAssociate')->with(['loanBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->where('loan_type','!=',3)->where('associate_member_id',$member->id);



                    



                    $loan_data1=$loan_data->orderby('created_at','DESC')->get();

                    $count=count($loan_data1);

                    if($request->page==1)

                    {

                        $start=0;

                    }

                    else

                    {

                        $start=$request->page*$request->length;

                    }

                    

                    $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();



                    foreach ($loan_data as $key => $row) 

                    {

                            $data[$key]['id'] = $row->id;

                            if ( $row->group_activity == 'Group loan application' ) 

                            {

                                if ( $row->groupleader_member_id ) {

                                    $applicant_id= Member::find($row->groupleader_member_id)->member_id;

                                } else {

                                    $applicant_id='';

                                }

                            } else {

                                if ( $row->applicant_id ) {

                                    $applicant_id= Member::find($row->applicant_id)->member_id;

                                } else {

                                   $applicant_id= '';

                                }

                            }

                        $data[$key]['applicant_id'] =  $applicant_id;

                        $data[$key]['account_number'] = $row->account_number;

                        $data[$key]['br_name'] = $row['loanBranch']->name;

                        $data[$key]['br_code'] = $row['loanBranch']->branch_code;

                        $data[$key]['so_name'] = $row['loanBranch']->sector;

                        $data[$key]['ro_name'] = $row['loanBranch']->regan;

                        $data[$key]['zo_name'] = $row['loanBranch']->zone;

                        $data[$key]['member_id'] = $row['loanMember']->member_id;

                        $data[$key]['member_name'] = $row['loanMember']->first_name.' '.$row['loanMember']->last_name;

                        $data[$key]['last_recovery_date'] = date("d/m/Y", strtotime( $row->closing_date));

                        $data[$key]['associate_code'] = getMemberData($row->associate_member_id)->associate_no;

                        $m =Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);

                        $associate_name = $m->first_name.' '.$m->last_name;

                        $data[$key]['associate_name'] = $associate_name;

                        $data[$key]['loan_type'] =$row['loan']->name;
                        $data[$key]['loan_type_int'] =$row->loan_type;

                        $data[$key]['amount'] =$row->amount;
                        $data[$key]['transfer_amount'] =$row->deposite_amount;
                        $data[$key]['file_charge'] =$row->file_charges;
                        $data[$key]['loan_amount'] =$row->amount;

                        if($row->status == 0){

                            $status = 'Pending';

                        }else if($row->status == 1){

                            $status = 'Approved';

                        }else if($row->status == 2){

                            $status = 'Rejected';

                        }else if($row->status == 3){

                            $status = 'Clear';

                        }else if($row->status == 4){

                            $status = 'Due';

                        }

                        $data[$key]['status'] = $status;

                        if($row['approve_date']){

                            $data[$key]['approved_date'] = date("d/m/Y", strtotime( $row['approve_date']));

                        }

                        else

                        {

                            $data[$key]['approved_date'] = 'N/A';

                        }



                        $data[$key]['application_date'] = date("d/m/Y", strtotime( $row['created_at']));

                        

                    }



                    $status   = "Success";

                    $code     = 200;

                    $messages = 'Loan listing!';

                    $page  = $request->page;

                    $length  = $request->length;

                    $result   = ['member' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];

                    $associate_status=$member->associate_app_status;

                    

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                    }

                    else

                    {

                        $status = "Error";

                        $code = 201;

                        $messages = 'Page no or length must be grater than 0!';

                        $result = '';

                        $associate_status=$member->associate_app_status;

                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                    }

                    

                }else{

                    $status = "Error";

                    $code = 201;

                    $messages = 'API token mismatch!';

                    $result = '';

                    $associate_status=$member->associate_app_status;

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                }

            }else{

                $status = "Error";

                $code = 201;

                $messages = 'Something went wrong!';

                $result = '';

                $associate_status=9;

                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

            } 

        } catch (Exception $e) {

            $status = "Error";

            $code = 500;

            $messages = $e->getMessage();

            $result = '';

            $associate_status=9;

            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

        }

    }





    public function groupLoanList(Request $request)

    {   

        $associate_no = $request->associate_no;  

        

        try { 



        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();



            if ($member) {

                $token = md5($request->associate_no);

                if($token == $request->token){

                    if($request->page>0 && $request->length>0)

                    {

                        $data = array();



                        



                    $loan_data = Grouploans::with('loanMember','loanMemberAssociate')->with(['gloanBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->where('associate_member_id',$member->id);



                    



                    $loan_data1=$loan_data->orderby('created_at','DESC')->get();

                    $count=count($loan_data1);

                    if($request->page==1)

                    {

                        $start=0;

                    }

                    else

                    {

                        $start=$request->page*$request->length;

                    }

                    

                    $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();



                    foreach ($loan_data as $key => $row) 

                    {

                            $data[$key]['id'] = $row->id;

                            if ( $row->group_activity == 'Group loan application' ) 

                            {

                                if ( $row->groupleader_member_id ) {

                                    $applicant_id= Member::find($row->groupleader_member_id)->member_id;

                                } else {

                                    $applicant_id='';

                                }

                            } else {

                                if ( $row->applicant_id ) {

                                    $applicant_id= Member::find($row->applicant_id)->member_id;

                                } else {

                                   $applicant_id= '';

                                }

                            }

                        $data[$key]['applicant_id'] =  $applicant_id;

                        $data[$key]['group_loan_id'] = $row->group_loan_common_id;

                        $data[$key]['account_number'] = $row->account_number;

                        $data[$key]['br_name'] = $row['gloanBranch']->name;

                        $data[$key]['br_code'] = $row['gloanBranch']->branch_code;

                        $data[$key]['so_name'] = $row['gloanBranch']->sector;

                        $data[$key]['ro_name'] = $row['gloanBranch']->regan;

                        $data[$key]['zo_name'] = $row['gloanBranch']->zone;

                        $data[$key]['member_id'] = $row['loanMember']->member_id;

                        $data[$key]['member_name'] = $row['loanMember']->first_name.' '.$row['loanMember']->last_name;

                        $data[$key]['last_recovery_date'] = date("d/m/Y", strtotime( $row->closing_date));

                        $data[$key]['associate_code'] = getMemberData($row->associate_member_id)->associate_no;

                        $m =Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);

                        $associate_name = $m->first_name.' '.$m->last_name;

                        $data[$key]['associate_name'] = $associate_name;

                        $data[$key]['loan_type'] ='Group Loan';
                        $data[$key]['loan_type_int'] =3;

                        $data[$key]['amount'] =$row->amount;
                        $data[$key]['transfer_amount'] =$row->deposite_amount;
                        $data[$key]['file_charge'] =$row->file_charges;
                        $data[$key]['loan_amount'] =$row->amount;

                        if($row->status == 0){

                            $status = 'Pending';

                        }else if($row->status == 1){

                            $status = 'Approved';

                        }else if($row->status == 2){

                            $status = 'Rejected';

                        }else if($row->status == 3){

                            $status = 'Clear';

                        }else if($row->status == 4){

                            $status = 'Due';

                        }

                        $data[$key]['status'] = $status;

                        if($row['approve_date']){

                            $data[$key]['approved_date'] = date("d/m/Y", strtotime( $row['approve_date']));

                        }

                        else

                        {

                            $data[$key]['approved_date'] = 'N/A';

                        }



                        $data[$key]['application_date'] = date("d/m/Y", strtotime( $row['created_at']));

                        

                    }



                    $status   = "Success";

                    $code     = 200;

                    $messages = 'Group Loan listing!';

                    $page  = $request->page;

                    $length  = $request->length;

                    $result   = ['member' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];

                    $associate_status=$member->associate_app_status;

                    

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                    }

                    else

                    {

                        $status = "Error";

                        $code = 201;

                        $messages = 'Page no or length must be grater than 0!';

                        $result = '';

                        $associate_status=$member->associate_app_status;

                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                    }

                    

                }else{

                    $status = "Error";

                    $code = 201;

                    $messages = 'API token mismatch!';

                    $result = '';

                    $associate_status=$member->associate_app_status;

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

                }

            }else{

                $status = "Error";

                $code = 201;

                $messages = 'Something went wrong!';

                $result = '';

                $associate_status=9;

                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

            } 

        } catch (Exception $e) {

            $status = "Error";

            $code = 500;

            $messages = $e->getMessage();

            $result = '';

            $associate_status=9;

            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);

        }

    }


    public function associateDetails(Request $request)
    {   
        $associate_no = $request->associate_no; 
        $associate_code = $request->associate_code; 
        $applicationDate = $request->applicationDate; 
        try { 

            $assomember = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();    

            if ($assomember) {
                $token = md5($request->associate_no);
                if($token == $request->token){

                    $member = Member::with('savingAccount')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
                    ->where('members.associate_no',$associate_code)
                    ->where('members.status',1)
                    ->where('members.is_deleted',0)
                    ->where('members.is_associate',1)
                    ->where('members.is_block',0)
                    ->where('members.associate_status',1)
                    ->select('carders.name as carders_name','members.first_name','members.last_name','members.id')
                    ->first();

                    if($member){
                        if($member['savingAccount']){
                            $ssbTransaction = SavingAccountTranscation::select('opening_balance')->where('account_no',$member['savingAccount'][0]->account_no)->whereDate('created_at',date("Y-m-d", strtotime(convertDate($applicationDate))))->first();
                            $ssbAccountNumber = $member['savingAccount'][0]->account_no;
                            $ssbId = $member['savingAccount'][0]->id;
                            if($ssbTransaction){
                                $ssbAmount = $ssbTransaction->opening_balance;
                            }else{
                                $ssbAmount = 0;
                            }
                        }else{
                            $ssbAmount = 0;
                            $ssbAccountNumber = '';
                            $ssbId = '';
                        }
                        
                        $fName = $member->first_name ? $member->first_name : '';
                        $lName = $member->last_name ? $member->last_name : '';
                        $data['name'] = $fName.' '.$lName;
                        $data['memberId'] = $member->id;
                        $data['ssbAccountNumber'] = $ssbAccountNumber;
                        $data['ssbAmount'] = $ssbAmount;
                        $data['ssbId'] = $ssbId;

                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate details!';
                        $result   = $data;
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }else{
                        $status = "Error";
                        $code = 404;
                        $messages = 'Not Found!';
                        $result = '';
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);    
                    }
                }else{

                    $status = "Error";
                    $code = 201;
                    $messages = 'Something went wrong!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            }else{

                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            } 

        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);

        }
    }

    public function depositEmi(Request $request)
    {   
        $associate_no = $request->associate_no;
        $associate_code = $request->associate_code;
        $deposiDate = $request->depositDate;
        $loanEmiPaymentMode = $request->loanEmiPaymentMode;
        $depositAmount = $request->depositAmount;
        $loanId = $request->loanId;  
        $penaltyAmount = $request->penaltyAmount;

        /*DB::beginTransaction();

        try {*/

            try { 

                $assomember = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();    

                

                if ($assomember) {
                    $token = md5($request->associate_no);
                    if($token == $request->token){
                        $entryTime = date("h:i:s");
                        Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));  
                        $member = Member::select('id','associate_app_status','first_name','last_name')->with('savingAccount')->where('associate_no',$request->associate_code)->where('associate_status',1)->where('is_block',0)->first();

                        if($member){
                            if($member['savingAccount']){
                                $ssbId = $member['savingAccount'][0]->id;
                            }else{
                                $status = "Error";
                                $code = 201;
                                $messages = 'Does not have an ssb account!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }

                            $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id',$ssbId)->first();
                            if($loanEmiPaymentMode == 0 && $ssbAccountDetails->balance < $depositAmount){
                                $status = "Error";
                                $code = 201;
                                $messages = 'Insufficient balance in ssb account!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }else{
                                $mLoan = Memberloans::with('loanMember')->where('id',$loanId)->first(); 

                                if($mLoan->emi_option == 1){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/1200;       
                                }elseif($mLoan->emi_option == 2){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/5200; 
                                }elseif($mLoan->emi_option == 3){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/36500;
                                }           
                                $principal_amount = $depositAmount-$roi;  
                                $amountArraySsb=array('1'=>$depositAmount);            
                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                                $dueAmount = $mLoan->due_amount-round($principal_amount);
                                $mlResult = Memberloans::find($loanId);
                                $lData['credit_amount'] = $mLoan->credit_amount+$principal_amount;
                                $lData['due_amount'] = $dueAmount;
                                if($dueAmount == 0){
                                    $lData['status'] = 3;
                                    $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }
                                $lData['received_emi_amount'] = $mLoan->received_emi_amount+$depositAmount;
                                $mlResult->update($lData);

                                $postData = $_POST;
                                $enData = array("post_data" => $postData, "lData" => $lData);
                                $encodeDate = json_encode($enData);
                                $arrs = array("load_id" => $loanId, "type" => "7", "account_head_id" => 0, "user_id" => $ssbAccountDetails['ssbMember']->id, "message" => "Loan Recovery   - Loan EMI payment", "data" => $encodeDate);
                                DB::table('user_log')->insert($arrs);

                                if($loanEmiPaymentMode == 0){
                                    $cheque_dd_no=NULL;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $bank_name=NULL;
                                    $cheque_date=NULL;
                                    $account_number=NULL;
                                    $paymentMode=4;
                                    $ssbpaymentMode=3;
                                    $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));

                                    $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->where('created_at','<',date("Y-m-d", strtotime(convertDate($deposiDate))))->first();

                                    $ssb['saving_account_id']=$ssbAccountDetails->id;
                                    $ssb['account_no']=$ssbAccountDetails->account_no;
                                    $ssb['opening_balance']=$record1->opening_balance-$depositAmount;
                                    $ssb['branch_id']=$mLoan->branch_id;
                                    $ssb['type']=9;
                                    $ssb['deposit']=0;
                                    $ssb['withdrawal']=$depositAmount;
                                    $ssb['description']='Loan EMI Payment';
                                    $ssb['currency_code']='INR';
                                    $ssb['payment_type']='DR';
                                    $ssb['payment_mode']=$ssbpaymentMode;
                                    $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                    $ssb_transaction_id = $ssbAccountTran->id;     
                                    // update saving account current balance 

                                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                    $ssbBalance->balance=$ssbAccountDetails->balance-$depositAmount;
                                    $ssbBalance->save();

                                    $record2=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->where('created_at','>',date("Y-m-d", strtotime(convertDate($deposiDate))))->get();   
                                    

                                    foreach ($record2 as $key => $value) {
                                        $savingResult = SavingAccountTranscation::find($value->id);
                                        $nsResult['opening_balance']=$value->opening_balance-$depositAmount;
                                        $nsResult['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                        $savingResult->update($nsResult);
                                    }

                                    $data['saving_account_transaction_id']=$ssb_transaction_id;
                                    $data['loan_id']=$loanId;

                                    $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $satRef = TransactionReferences::create($data);
                                    $satRefId = $satRef->id;

                                    $updateSsbDayBook = $this->updateSsbDayBookAmount($depositAmount,$ssbAccountDetails->account_no,date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));


                                    $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');

                                    $totalbalance = $ssbAccountDetails->balance-$depositAmount;

                                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$depositAmount,'Withdrawal from SSB',$ssbAccountDetails->account_no,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
                                }elseif($loanEmiPaymentMode == 1){

                                    if($request->bankTransferMode == 0){
                                        $cheque_dd_no=$request->customerCheque;
                                        $paymentMode=1;
                                        $ssbpaymentMode=5;
                                        $online_payment_id=NULL;
                                        $online_payment_by=NULL;
                                        $satRefId = NULL;
                                        $bank_name=NULL;
                                        $cheque_date=NULL;
                                        $account_number=NULL;
                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                    }elseif($request->bankTransferMode == 1){
                                        $cheque_dd_no=NULL;
                                        $paymentMode=3;
                                        $ssbpaymentMode=5;
                                        $online_payment_id=$request->utrTransactionNumber;
                                        $online_payment_by=NULL;
                                        $satRefId = NULL;
                                        $bank_name=NULL;
                                        $cheque_date=NULL;
                                        $account_number=NULL;
                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));

                                    }
                                }elseif($loanEmiPaymentMode == 2){
                                    $cheque_dd_no=NULL;
                                    $cheque_date=NULL;
                                    $account_number=NULL;
                                    $paymentMode=0;
                                    $ssbpaymentMode=0;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $satRefId = NULL;
                                    $bank_name=NULL;
                                    $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }elseif($loanEmiPaymentMode == 3){
                                    $cheque_dd_no=$request->chequeNumber;
                                    $cheque_date=$request->chequeDate;
                                    $bank_name=$request->bankName;
                                    $account_number=$request->accountNumber;
                                    $paymentMode=1;
                                    $ssbpaymentMode=1;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $satRefId = NULL;
                                    $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }

                                $ssbCreateTran = CommanController::createTransaction($satRefId,5,$loanId,$mLoan->applicant_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');

                                $createDayBook = $this->createDayBook($ssbCreateTran,$satRefId,5,$loanId,0,$mLoan->applicant_id,$dueAmount,$depositAmount,0,'Loan EMI deposit',$mLoan->account_number,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR',$member->id,1);

                                if($loanEmiPaymentMode == 3){
                                    $checkData['type']=4;
                                    $checkData['branch_id']=$mLoan->branch_id;
                                    $checkData['loan_id']=$loanId;
                                    $checkData['day_book_id']=$createDayBook;
                                    $checkData['cheque_id']=$cheque_dd_no;
                                    $checkData['status']=1;
                                    $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $ssbAccountTran = ReceivedChequePayment::create($checkData); 
                                    $dataRC['status']=3;
                                    $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                                    $receivedcheque->update($dataRC);
                                }

                                /************* Head Implement ****************/

                                if($loanEmiPaymentMode == 0){
                                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                    $v_no = "";
                                    for ($i = 0; $i < 10; $i++) {
                                        $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
                                    }      
                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);

                                    $principalbranchDayBook = CommanController::createBranchDayBook($roidayBookRef,$mLoan->branch_id,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$associate_id=NULL,$member->id,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$associate_id=NULL,$member->id,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 

                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roimemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$ssb_transaction_id,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);

                                }elseif($loanEmiPaymentMode == 2){  

                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);

                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);


                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    $principalbranchDayBook = CommanController::createBranchDayBook($roidayBookRef,$mLoan->branch_id,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Cash A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalmemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi+$principal_amount,$mLoan->branch_id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))));
                                }elseif($loanEmiPaymentMode == 1){



                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    if($request->bankTransferMode == 0){

                                        $payment_type = 1;

                                        $amount_from_id =$member->id;

                                        $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                        $cheque_no = $request->customerCheque;

                                        $cheque_date = NULL;

                                        $cheque_bank_from = $request->customerBankName;

                                        $cheque_bank_ac_from = $request->customerBankAccountNumber;

                                        $cheque_bank_ifsc_from = $request->customerIfscCode;

                                        $cheque_bank_branch_from=NULL;

                                        $cheque_bank_to=$request->companyBank;

                                        $cheque_bank_ac_to=$request->bankAccountNumber;

                                        $v_no=NULL;

                                        $v_date=NULL;

                                        $ssb_account_id_from=NULL;

                                        $transction_no = NULL;

                                        $transction_bank_from = NULL;

                                        $transction_bank_ac_from = NULL;

                                        $transction_bank_ifsc_from = NULL;

                                        $transction_bank_branch_from = NULL;

                                        $transction_bank_to = NULL;

                                        $transction_bank_ac_to = NULL;

                                    }elseif($request->bankTransferMode == 1){

                                        $payment_type = 2;

                                        $amount_from_id =$member->id;

                                        $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                        $cheque_no = NULL;

                                        $cheque_date = NULL;

                                        $cheque_bank_from = NULL;

                                        $cheque_bank_ac_from = NULL;

                                        $cheque_bank_ifsc_from = NULL;

                                        $cheque_bank_branch_from = NULL;

                                        $cheque_bank_to = NULL;

                                        $cheque_bank_ac_to = NULL;

                                        $transction_no = $request->utrTransactionNumber;

                                        $v_no=NULL;

                                        $v_date=NULL;

                                        $ssb_account_id_from=NULL;

                                        $transction_bank_from = $request->customerBankName;

                                        $transction_bank_ac_from = $customer_bank_account_number;

                                        $transction_bank_ifsc_from = $request->customerIfscCode;

                                        $transction_bank_branch_from = $request->customerBranchName;

                                        $transction_bank_to = $request->companyBank;

                                        $transction_bank_ac_to = $request->bankAccountNumber;

                                    }



                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $allTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $allTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalmemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$branch_id=NULL,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($roidayBookRef,$bank_id=NULL,$account_id=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,'EMI collection','Cash A/C Cr. '.($roi+$principal_amount).'','Cash A/C Cr. '.($roi+$principal_amount).'','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$amount_from_name,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($roi+$principal_amount,$request->companyBank,getSamraddhBankAccount($request->bankAccountNumber)->id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))),0,0);
                                }

                                /************* Head Implement ****************/

                                /*---------- commission script  start  ---------*/

                                $daybookId=$createDayBook;

                                $total_amount=$depositAmount;

                                $percentage=2;                    

                                $month=NULL;

                                $type_id=$loanId;

                                $type=4;

                                $associate_id=$member->id;

                                $branch_id=$mLoan->branch_id;

                                $commission_type=0;

                                $associateDetail=Member::where('id',$associate_id)->first();

                                $carder=$associateDetail->current_carder_id;

                                $associate_exist=0; 

                                $percentInDecimal = $percentage / 100;

                                $commission_amount = round($percentInDecimal * $total_amount,4);

                                $associateCommission['member_id'] = $associate_id;

                                $associateCommission['branch_id'] = $branch_id;

                                $associateCommission['type'] = $type;

                                $associateCommission['type_id'] = $type_id; 

                                $associateCommission['day_book_id'] = $daybookId; 

                                $associateCommission['total_amount'] = $total_amount;

                                $associateCommission['month'] = $month;

                                $associateCommission['commission_amount'] = $commission_amount;

                                $associateCommission['percentage'] = $percentage;  

                                $associateCommission['commission_type'] = $commission_type;

                                $date =\App\Models\Daybook::where('id',$daybookId)->first();

                                $associateCommission['created_at'] = $date->created_at;                    

                                $associateCommission['pay_type'] = 4;

                                $associateCommission['carder_id'] = $carder; 

                                $associateCommission['associate_exist'] = $associate_exist;                

                                $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);

                                $createDayBook = $this->createLoanDayBook($mLoan->loan_type,0,$loanId,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$roi,$principal_amount,$dueAmount,$depositAmount,'Loan EMI deposit',$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$member->first_name.' '.$member->last_name,$member->id,$mLoan->branch_id,$member->id,1);

                                if($penaltyAmount != '' && isset($penaltyAmount)){

                                    $amountArray=array('1'=>$penaltyAmount);

                                    if($loanEmiPaymentMode == 0){

                                        $cheque_dd_no=NULL;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentMode=4;

                                        $ssbpaymentMode=3;

                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));



                                        $ssb['saving_account_id']=$ssbAccountDetails->id;

                                        $ssb['account_no']=$ssbAccountDetails->account_no;

                                        $ssb['opening_balance']=$ssbAccountDetails->balance-$penaltyAmount;

                                        $ssb['deposit']=0;

                                        $ssb['withdrawal']=$penaltyAmount;

                                        $ssb['description']='Loan EMI Penalty';

                                        $ssb['currency_code']='INR';

                                        $ssb['payment_type']='DR';

                                        $ssb['payment_mode']=$ssbpaymentMode;

                                        $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);

                                        $ssb_transaction_id = $ssbAccountTran->id;     

                                        // update saving account current balance 



                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                                        $ssbBalance->balance=$ssbAccountDetails->balance-$penaltyAmount;

                                        $ssbBalance->save();



                                        $data['saving_account_transaction_id']=$ssb_transaction_id;

                                        $data['loan_id']=$loanId;

                                        $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $satRef = TransactionReferences::create($data);

                                        $satRefId = $satRef->id;



                                        $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');



                                        $totalbalance = $ssbAccountDetails->balance-$penaltyAmount;

                                    

                                        $createDayBook = $this->createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$penaltyAmount,'Withdrawal from SSB',$ssbAccountDetails->account_no,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR',$member->id,1);

                                    }elseif($loanEmiPaymentMode == 1){

                                        if($request->bankTransferMode == 0){

                                            $paymentMode = 1;

                                            $cheque_dd_no = $request->customerCheque;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = NULL;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }elseif($request->bankTransferMode == 1){

                                            $paymentMode = 2;

                                            $cheque_dd_no = NULL;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = $request->utrTransactionNumber;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }

                                    }elseif($loanEmiPaymentMode == 2){

                                        $cheque_dd_no=NULL;

                                        $paymentMode=0;

                                        $ssbpaymentMode=0;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }



                                    $ssbCreateTran = CommanController::createTransaction($satRefId,11,$loanId,$mLoan->applicant_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');

                            

                                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,11,$loanId,0,$mLoan->applicant_id,$dueAmount,$penaltyAmount,0,'Loan EMI penalty',$mLoan->account_number,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');



                                    if($loanEmiPaymentMode == 3){

                                        $checkData['type']=4;

                                        $checkData['branch_id']=$mLoan->branch_id;

                                        $checkData['loan_id']=$loanId;

                                        $checkData['day_book_id']=$createDayBook;

                                        $checkData['cheque_id']=$cheque_dd_no;

                                        $checkData['status']=1;

                                        $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $ssbAccountTran = ReceivedChequePayment::create($checkData); 



                                        $dataRC['status']=3;

                                        $receivedcheque = ReceivedCheque::find($cheque_dd_no);

                                        $receivedcheque->update($dataRC);



                                    }



                                    /************* Head Implement ****************/

                                    if($loanEmiPaymentMode == 0){

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                                        $v_no = "";

                                        for ($i = 0; $i < 10; $i++) {

                                            $v_no .= $chars[mt_rand(0, strlen($chars)-1)];

                                        }      



                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roibranchDayBook = CommanController::createBranchDayBook($penaltyDayBookRef,$mLoan->branch_id,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','SSB A/C Cr '.$penaltyAmount.'','SSB A/C Cr '.$penaltyAmount.'','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roimemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$ssb_transaction_id,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 

                                    }elseif($loanEmiPaymentMode == 2){  

                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roibranchDayBook = CommanController::createBranchDayBook($penaltyDayBookRef,$mLoan->branch_id,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','Cash A/C Dr '.$penaltyAmount.'','To '.$mLoan->account_number.' A/C Cr '.$penaltyAmount.'','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roimemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 



                                        $createPenaltyBranchCash = $this->updateBranchCashFromBackDate($penaltyAmount,$mLoan->branch_id,date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));



                                    }elseif($loanEmiPaymentMode == 1){



                                        if($request->bankTransferMode == 0){

                                            $payment_type = 1;

                                            $amount_from_id =$member->id;

                                            $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                            $cheque_no = $request->customerCheque;

                                            $cheque_date = NULL;

                                            $cheque_bank_from = $request->customerBankName;

                                            $cheque_bank_ac_from = $request->customerBankAccountNumber;

                                            $cheque_bank_ifsc_from = $request->customerIfscCode;

                                            $cheque_bank_branch_from=NULL;

                                            $cheque_bank_to=$request->companyBank;

                                            $cheque_bank_ac_to=$request->bankAccountNumber;

                                            $v_no=NULL;

                                            $v_date=NULL;

                                            $ssb_account_id_from=NULL;

                                            $transction_no = NULL;

                                            $transction_bank_from = NULL;

                                            $transction_bank_ac_from = NULL;

                                            $transction_bank_ifsc_from = NULL;

                                            $transction_bank_branch_from = NULL;

                                            $transction_bank_to = NULL;

                                            $transction_bank_ac_to = NULL;

                                        }elseif($request->bankTransferMode == 1){

                                            $payment_type = 2;

                                            $amount_from_id =$member->id;

                                            $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                            $cheque_no = NULL;

                                            $cheque_date = NULL;

                                            $cheque_bank_from = NULL;

                                            $cheque_bank_ac_from = NULL;

                                            $cheque_bank_ifsc_from = NULL;

                                            $cheque_bank_branch_from = NULL;

                                            $cheque_bank_to = NULL;

                                            $cheque_bank_ac_to = NULL;

                                            $transction_no = $request->utrTransactionNumber;

                                            $v_no=NULL;

                                            $v_date=NULL;

                                            $ssb_account_id_from=NULL;

                                            $transction_bank_from = $request->customerBankName;

                                            $transction_bank_ac_from = $request->customerBankAccountNumber;

                                            $transction_bank_ifsc_from = $request->customerIfscCode;

                                            $transction_bank_branch_from = $request->customerBranchName;

                                            $transction_bank_to = $request->companyBank;

                                            $transction_bank_ac_to = $request->bankAccountNumber;

                                        }



                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$request->bankAccountNumber,getSamraddhBank($bankAccountNumber)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);                    
                                        $allTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $principalmemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($penaltyDayBookRef,$bank_id=NULL,$account_id=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','Cash A/C Cr. '.$penaltyAmount.'','Cash A/C Cr. '.$penaltyAmount.'','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$amount_from_name,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($penaltyAmount,$request->companyBank,getSamraddhBankAccount($request->bankAccountNumber)->id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))),0,0);

                                    }

                                    /************* Head Implement ****************/

                                    if($loanEmiPaymentMode == 0){

                                        $paymentMode = 4;

                                        $cheque_dd_no = NULL;

                                        $ssbpaymentMode=5;

                                        $online_payment_id = NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }elseif($loanEmiPaymentMode == 1){

                                        if($request->bankTransferMode == 0){

                                            $paymentMode = 1;

                                            $cheque_dd_no = $request->customerCheque;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = NULL;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }elseif($request->bankTransferMode == 1){

                                            $paymentMode = 2;

                                            $cheque_dd_no = NULL;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = $request->utrTransactionNumber;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }

                                    }elseif($loanEmiPaymentMode == 2){

                                        $cheque_dd_no=NULL;

                                        $paymentMode=0;

                                        $ssbpaymentMode=0;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }



                                    $createDayBook = $this->createLoanDayBook($mLoan->loan_type,1,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$penaltyAmount,$penaltyAmount,$opening_balance=NULL,$penaltyAmount,'Loan EMI penalty',$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$member->first_name.' '.$member->last_name,$member->id,$mLoan->branch_id,$member->id,1);  

                                }

                                $status = "Success";
                                $code = 200;
                                $messages = 'Successfully pay loan emi!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }
                        }else{
                            $status = "Error";
                            $code = 201;
                            $messages = 'Something went wrong!';
                            $result = '';
                            return response()->json(compact('status', 'code', 'messages', 'result'), $code);    
                        }

                    }else{

                        $status = "Error";
                        $code = 201;
                        $messages = 'API token mismatch!';
                        $result = '';
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }
                }else{

                    $status = "Error";
                    $code = 201;
                    $messages = 'Something went wrong!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } 

            } catch (Exception $e) {
                $status = "Error";
                $code = 500;
                $messages = $e->getMessage();
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);

            }
        /*DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 
            $status = "Error";
            $code = 500;
            $messages = $ex->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);

        }*/
    }

    public function depositGroupLoanEmi(Request $request)
    {   
        $associate_no = $request->associate_no;
        $associate_code = $request->associate_code;
        $deposiDate = $request->depositDate;
        $loanEmiPaymentMode = $request->loanEmiPaymentMode;
        $depositAmount = $request->depositAmount;
        $loanId = $request->loanId;  
        $penaltyAmount = $request->penaltyAmount;
        /*DB::beginTransaction();

        try {*/

            try { 

                $assomember = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();    

                if ($assomember) {
                    $token = md5($request->associate_no);
                    if($token == $request->token){
                        $entryTime = date("h:i:s");
                        Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));    
                        $member = Member::select('id','associate_app_status','first_name','last_name')->with('savingAccount')->where('associate_no',$request->associate_code)->where('associate_status',1)->where('is_block',0)->first();
                        if($member){
                            if($member['savingAccount']){
                                $ssbId = $member['savingAccount'][0]->id;
                            }else{
                                $status = "Error";
                                $code = 201;
                                $messages = 'Does not have an ssb account!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }  

                            $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id',$ssbId)->first();
                            if($loanEmiPaymentMode == 0 && $ssbAccountDetails->balance < $depositAmount){
                                $status = "Error";
                                $code = 201;
                                $messages = 'Insufficient balance in ssb account!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }else{
                                $mLoan = Grouploans::with('loanMember')->where('id',$request['loan_id'])->first(); 

                                if($mLoan->emi_option == 1){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/1200;       
                                }elseif($mLoan->emi_option == 2){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/5200; 
                                }elseif($mLoan->emi_option == 3){
                                    $roi = $mLoan->due_amount*$mLoan->ROI/36500;
                                }           
                                $principal_amount = $depositAmount-$roi;  
                                $amountArraySsb=array('1'=>$depositAmount);            
                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                                $dueAmount = $mLoan->due_amount-round($principal_amount);
                                $glResult = Grouploans::find($loanId);
                                $glData['credit_amount'] = $mLoan->credit_amount+$principal_amount;
                                $glData['due_amount'] = $dueAmount;
                                if($dueAmount == 0){
                                    $glData['status'] = 3;
                                    $glData['clear_date'] = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }
                                $glResult['received_emi_amount'] = $mLoan->received_emi_amount+$depositAmount;
                                $glResult->update($glData);

                                $gmLoan = Memberloans::with('loanMember')->where('id',$mLoan->member_loan_id)->first();
                                $gmDueAmount = $gmLoan->due_amount-$principal_amount;
                                $mlResult = Memberloans::find($mLoan->member_loan_id);
                                $lData['credit_amount'] = $mLoan->credit_amount+$principal_amount;
                                $lData['due_amount'] = $gmDueAmount;
                                if($dueAmount == 0){
                                    $lData['status'] = 3;
                                    $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));    

                                }
                                $mlResult->update($lData);

                                $postData = $_POST;
                                $enData = array("post_data" => $postData, "lData" => $glData);
                                $encodeDate = json_encode($enData);
                                $arrs = array("load_id" => $loanId, "type" => "7", "account_head_id" => 0, "user_id" => $member->id, "message" => "Loan Recovery   - Loan EMI payment", "data" => $encodeDate);
                                DB::table('user_log')->insert($arrs);

                                if($loanEmiPaymentMode == 0){
                                    $cheque_dd_no=NULL;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $bank_name=NULL;
                                    $cheque_date=NULL;
                                    $account_number=NULL;
                                    $paymentMode=4;
                                    $ssbpaymentMode=3;
                                    $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));

                                    $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->where('created_at','<',date("Y-m-d", strtotime(convertDate($deposiDate))))->first();

                                    $ssb['saving_account_id']=$ssbAccountDetails->id;
                                    $ssb['account_no']=$ssbAccountDetails->account_no;
                                    $ssb['opening_balance']=$record1->opening_balance-$depositAmount;
                                    $ssb['branch_id']=$mLoan->branch_id;
                                    $ssb['type']=9;
                                    $ssb['deposit']=0;
                                    $ssb['withdrawal']=$depositAmount;
                                    $ssb['description']='Loan EMI Payment';
                                    $ssb['currency_code']='INR';
                                    $ssb['payment_type']='DR';
                                    $ssb['payment_mode']=$ssbpaymentMode;
                                    $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                    $ssb_transaction_id = $ssbAccountTran->id;     
                                    // update saving account current balance 

                                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                    $ssbBalance->balance=$ssbAccountDetails->balance-$depositAmount;
                                    $ssbBalance->save();

                                    $record2=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->where('created_at','>',date("Y-m-d", strtotime(convertDate($deposiDate))))->get();   
                                    foreach ($record2 as $key => $value) {
                                        $savingResult = SavingAccountTranscation::find($value->id);
                                        $nsResult['opening_balance']=$value->opening_balance-$depositAmount;
                                        $nsResult['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                        $savingResult->update($nsResult);
                                    }

                                    $data['saving_account_transaction_id']=$ssb_transaction_id;
                                    $data['loan_id']=$loanId;

                                    $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $satRef = TransactionReferences::create($data);
                                    $satRefId = $satRef->id;

                                    $updateSsbDayBook = $this->updateSsbDayBookAmount($depositAmount,$ssbAccountDetails->account_no,date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));

                                    $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');

                                    $totalbalance = $ssbAccountDetails->balance-$depositAmount;

                                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$depositAmount,'Withdrawal from SSB',$ssbAccountDetails->account_no,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');
                                }elseif($loanEmiPaymentMode == 1){

                                    if($request->bankTransferMode == 0){
                                        $cheque_dd_no=$request->customerCheque;
                                        $paymentMode=1;
                                        $ssbpaymentMode=5;
                                        $online_payment_id=NULL;
                                        $online_payment_by=NULL;
                                        $satRefId = NULL;
                                        $bank_name=NULL;
                                        $cheque_date=NULL;
                                        $account_number=NULL;
                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                    }elseif($bankTransferMode == 1){
                                        $cheque_dd_no=NULL;
                                        $paymentMode=3;
                                        $ssbpaymentMode=5;
                                        $online_payment_id=$request->utrTransactionNumber;
                                        $online_payment_by=NULL;
                                        $satRefId = NULL;
                                        $bank_name=NULL;
                                        $cheque_date=NULL;
                                        $account_number=NULL;
                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));

                                    }
                                }elseif($loanEmiPaymentMode == 2){
                                    $cheque_dd_no=NULL;
                                    $cheque_date=NULL;
                                    $account_number=NULL;
                                    $paymentMode=0;
                                    $ssbpaymentMode=0;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $satRefId = NULL;
                                    $bank_name=NULL;
                                    $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }elseif($loanEmiPaymentMode == 3){
                                    $cheque_dd_no=$request->chequeNumber;
                                    $cheque_date=$request->chequeDate;
                                    $bank_name=$request->bankName;
                                    $account_number=$request->accountNumber;
                                    $paymentMode=1;
                                    $ssbpaymentMode=1;
                                    $online_payment_id=NULL;
                                    $online_payment_by=NULL;
                                    $satRefId = NULL;
                                    $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));
                                }

                                $ssbCreateTran = CommanController::createTransaction($satRefId,5,$loanId,$mLoan->applicant_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');

                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,5,$loanId,0,$mLoan->applicant_id,$dueAmount,$depositAmount,0,'Loan EMI deposit',$mLoan->account_number,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArraySsb,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');

                                if($loanEmiPaymentMode == 3){
                                    $checkData['type']=4;
                                    $checkData['branch_id']=$mLoan->branch_id;
                                    $checkData['loan_id']=$loanId;
                                    $checkData['day_book_id']=$createDayBook;
                                    $checkData['cheque_id']=$cheque_dd_no;
                                    $checkData['status']=1;
                                    $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));
                                    $ssbAccountTran = ReceivedChequePayment::create($checkData); 
                                    $dataRC['status']=3;
                                    $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                                    $receivedcheque->update($dataRC);
                                }

                                /************* Head Implement ****************/

                                if($loanEmiPaymentMode == 0){
                                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                    $v_no = "";
                                    for ($i = 0; $i < 10; $i++) {
                                        $v_no .= $chars[mt_rand(0, strlen($chars)-1)];
                                    }      
                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);

                                    $principalbranchDayBook = CommanController::createBranchDayBook($roidayBookRef,$mLoan->branch_id,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','SSB A/C Cr '.($roi+$principal_amount).'','SSB A/C Cr '.($roi+$principal_amount).'','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 

                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,52,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roimemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$ssb_transaction_id,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);

                                }elseif($loanEmiPaymentMode == 2){  

                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);

                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);


                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    $principalbranchDayBook = CommanController::createBranchDayBook($roidayBookRef,$mLoan->branch_id,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','Cash A/C Dr '.($roi+$principal_amount).'','To '.$mLoan->account_number.' A/C Cr '.($roi+$principal_amount).'','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalmemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $createRoiBranchCash = $this->updateBranchCashFromBackDate($roi+$principal_amount,$mLoan->branch_id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))));
                                }elseif($loanEmiPaymentMode == 1){



                                    if($mLoan->loan_type == '1'){

                                        $loan_head_id = 64;

                                    }elseif($mLoan->loan_type == '2'){

                                        $loan_head_id = 65;

                                    }elseif($mLoan->loan_type == '3'){

                                        $loan_head_id = 66;        

                                    }elseif($mLoan->loan_type == '4'){

                                        $loan_head_id = 67;  

                                    }



                                    if($request->bankTransferMode == 0){

                                        $payment_type = 1;

                                        $amount_from_id =$member->id;

                                        $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                        $cheque_no = $request->customerCheque;

                                        $cheque_date = NULL;

                                        $cheque_bank_from = $request->customerBankName;

                                        $cheque_bank_ac_from = $request->customerBankAccountNumber;

                                        $cheque_bank_ifsc_from = $request->customerIfscCode;

                                        $cheque_bank_branch_from=NULL;

                                        $cheque_bank_to=$request->companyBank;

                                        $cheque_bank_ac_to=$request->bankAccountNumber;

                                        $v_no=NULL;

                                        $v_date=NULL;

                                        $ssb_account_id_from=NULL;

                                        $transction_no = NULL;

                                        $transction_bank_from = NULL;

                                        $transction_bank_ac_from = NULL;

                                        $transction_bank_ifsc_from = NULL;

                                        $transction_bank_branch_from = NULL;

                                        $transction_bank_to = NULL;

                                        $transction_bank_ac_to = NULL;

                                    }elseif($request->bankTransferMode == 1){

                                        $payment_type = 2;

                                        $amount_from_id =$member->id;

                                        $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                        $cheque_no = NULL;

                                        $cheque_date = NULL;

                                        $cheque_bank_from = NULL;

                                        $cheque_bank_ac_from = NULL;

                                        $cheque_bank_ifsc_from = NULL;

                                        $cheque_bank_branch_from = NULL;

                                        $cheque_bank_to = NULL;

                                        $cheque_bank_ac_to = NULL;

                                        $transction_no = $request->utrTransactionNumber;

                                        $v_no=NULL;

                                        $v_date=NULL;

                                        $ssb_account_id_from=NULL;

                                        $transction_bank_from = $request->customerBankName;

                                        $transction_bank_ac_from = $customer_bank_account_number;

                                        $transction_bank_ifsc_from = $request->customerIfscCode;

                                        $transction_bank_branch_from = $request->customerBranchName;

                                        $transction_bank_to = $request->companyBank;

                                        $transction_bank_ac_to = $request->bankAccountNumber;

                                    }



                                    $roidayBookRef = CommanController::createBranchDayBookReference($roi+$principal_amount);



                                    $roiallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,31,$head4=NULL,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $allTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$roi,$roi,$roi,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalallTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $allTransaction = CommanController::createAllTransaction($roidayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$principal_amount,$principal_amount,$principal_amount,''.$mLoan->account_number.' EMI collection','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $principalmemberTransaction = CommanController::createMemberTransaction($roidayBookRef,5,52,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$branch_id=NULL,$bank_id=NULL,$account_id=NULL,$roi+$principal_amount,''.$mLoan->account_number.' EMI collection','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($roidayBookRef,$bank_id=NULL,$account_id=NULL,5,52,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$roi+$principal_amount,$roi+$principal_amount,$roi+$principal_amount,'EMI collection','Cash A/C Cr. '.($roi+$principal_amount).'','Cash A/C Cr. '.($roi+$principal_amount).'','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$amount_from_name,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                    $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($roi+$principal_amount,$request->companyBank,getSamraddhBankAccount($request->bankAccountNumber)->id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))),0,0);
                                }

                                /************* Head Implement ****************/

                                /*---------- commission script  start  ---------*/

                                $daybookId=$createDayBook;

                                $total_amount=$depositAmount;

                                $percentage=2;                    

                                $month=NULL;

                                $type_id=$loanId;

                                $type=4;

                                $associate_id=$member->id;

                                $branch_id=$mLoan->branch_id;

                                $commission_type=0;

                                $associateDetail=Member::where('id',$associate_id)->first();

                                $carder=$associateDetail->current_carder_id;

                                $associate_exist=0; 

                                $percentInDecimal = $percentage / 100;

                                $commission_amount = round($percentInDecimal * $total_amount,4);

                                $associateCommission['member_id'] = $associate_id;

                                $associateCommission['branch_id'] = $branch_id;

                                $associateCommission['type'] = $type;

                                $associateCommission['type_id'] = $type_id; 

                                $associateCommission['day_book_id'] = $daybookId; 

                                $associateCommission['total_amount'] = $total_amount;

                                $associateCommission['month'] = $month;

                                $associateCommission['commission_amount'] = $commission_amount;

                                $associateCommission['percentage'] = $percentage;  

                                $associateCommission['commission_type'] = $commission_type;

                                $date =\App\Models\Daybook::where('id',$daybookId)->first();

                                $associateCommission['created_at'] = $date->created_at;                    

                                $associateCommission['pay_type'] = 4;

                                $associateCommission['carder_id'] = $carder; 

                                $associateCommission['associate_exist'] = $associate_exist;                

                                $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);

                                $createDayBook = $this->createLoanDayBook($mLoan->loan_type,0,$loanId,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$roi,$principal_amount,$dueAmount,$depositAmount,'Loan EMI deposit',$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$member->first_name.' '.$member->last_name,$member->id,$mLoan->branch_id,$member->id,1);

                                if($penaltyAmount != '' && isset($penaltyAmount)){

                                    $amountArray=array('1'=>$penaltyAmount);

                                    if($loanEmiPaymentMode == 0){

                                        $cheque_dd_no=NULL;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentMode=4;

                                        $ssbpaymentMode=3;

                                        $paymentDate = date("Y-m-d", strtotime(convertDate($deposiDate)));



                                        $ssb['saving_account_id']=$ssbAccountDetails->id;

                                        $ssb['account_no']=$ssbAccountDetails->account_no;

                                        $ssb['opening_balance']=$ssbAccountDetails->balance-$penaltyAmount;

                                        $ssb['deposit']=0;

                                        $ssb['withdrawal']=$penaltyAmount;

                                        $ssb['description']='Loan EMI Penalty';

                                        $ssb['currency_code']='INR';

                                        $ssb['payment_type']='DR';

                                        $ssb['payment_mode']=$ssbpaymentMode;

                                        $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $ssbAccountTran = SavingAccountTranscation::create($ssb);

                                        $ssb_transaction_id = $ssbAccountTran->id;     

                                        // update saving account current balance 



                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                                        $ssbBalance->balance=$ssbAccountDetails->balance-$penaltyAmount;

                                        $ssbBalance->save();



                                        $data['saving_account_transaction_id']=$ssb_transaction_id;

                                        $data['loan_id']=$loanId;

                                        $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $satRef = TransactionReferences::create($data);

                                        $satRefId = $satRef->id;



                                        $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');



                                        $totalbalance = $ssbAccountDetails->balance-$penaltyAmount;

                                    

                                        $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->member_investments_id,0,$ssbAccountDetails->member_id,$totalbalance,0,$penaltyAmount,'Withdrawal from SSB',$request->accountNumber,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$ssbAccountDetails->account_no,$cheque_dd_no,NULL,NULL,$paymentDate,$online_payment_by,$online_payment_by,$ssbAccountDetails->id,'DR');

                                    }elseif($loanEmiPaymentMode == 1){

                                        if($request->bankTransferMode == 0){

                                            $paymentMode = 1;

                                            $cheque_dd_no = $request->customerCheque;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = NULL;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }elseif($request->bankTransferMode == 1){

                                            $paymentMode = 2;

                                            $cheque_dd_no = NULL;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = $request->utrTransactionNumber;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }

                                    }elseif($loanEmiPaymentMode == 2){

                                        $cheque_dd_no=NULL;

                                        $paymentMode=0;

                                        $ssbpaymentMode=0;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }



                                    $ssbCreateTran = CommanController::createTransaction($satRefId,11,$loanId,$mLoan->applicant_id,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');

                            

                                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,11,$loanId,0,$mLoan->applicant_id,$dueAmount,$penaltyAmount,0,'Loan EMI penalty',$mLoan->account_number,$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,$amountArray,$paymentMode,$member->first_name.' '.$member->last_name,$member->id,$mLoan->account_number,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'CR');



                                    if($loanEmiPaymentMode == 3){

                                        $checkData['type']=4;

                                        $checkData['branch_id']=$mLoan->branch_id;

                                        $checkData['loan_id']=$loanId;

                                        $checkData['day_book_id']=$createDayBook;

                                        $checkData['cheque_id']=$cheque_dd_no;

                                        $checkData['status']=1;

                                        $checkData['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        $ssbAccountTran = ReceivedChequePayment::create($checkData); 



                                        $dataRC['status']=3;

                                        $receivedcheque = ReceivedCheque::find($cheque_dd_no);

                                        $receivedcheque->update($dataRC);



                                    }



                                    /************* Head Implement ****************/

                                    if($loanEmiPaymentMode == 0){

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                                        $v_no = "";

                                        for ($i = 0; $i < 10; $i++) {

                                            $v_no .= $chars[mt_rand(0, strlen($chars)-1)];

                                        }      



                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roibranchDayBook = CommanController::createBranchDayBook($penaltyDayBookRef,$mLoan->branch_id,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','SSB A/C Cr '.$penaltyAmount.'','SSB A/C Cr '.$penaltyAmount.'','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,1,8,20,56,$head5=NULL,5,53,$loanId,$ssb_transaction_id,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roimemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$ssb_transaction_id,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',2,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($deposiDate))),$ssbId,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 

                                    }elseif($loanEmiPaymentMode == 2){  

                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roibranchDayBook = CommanController::createBranchDayBook($penaltyDayBookRef,$mLoan->branch_id,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','Cash A/C Dr '.$penaltyAmount.'','To '.$mLoan->account_number.' A/C Cr '.$penaltyAmount.'','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$is_contra=NULL,$contra_id=NULL,$created_at=NULL,1);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $roimemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',0,'INR',$mLoan->branch_id,getBranchName($mLoan->branch_id)->name,$member->id,getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL); 



                                        $createPenaltyBranchCash = $this->updateBranchCashFromBackDate($penaltyAmount,$mLoan->branch_id,date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate))));



                                    }elseif($loanEmiPaymentMode == 1){



                                        if($request->bankTransferMode == 0){

                                            $payment_type = 1;

                                            $amount_from_id =$member->id;

                                            $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                            $cheque_no = $request->customerCheque;

                                            $cheque_date = NULL;

                                            $cheque_bank_from = $request->customerBankName;

                                            $cheque_bank_ac_from = $request->customerBankAccountNumber;

                                            $cheque_bank_ifsc_from = $request->customerIfscCode;

                                            $cheque_bank_branch_from=NULL;

                                            $cheque_bank_to=$request->companyBank;

                                            $cheque_bank_ac_to=$request->bankAccountNumber;

                                            $v_no=NULL;

                                            $v_date=NULL;

                                            $ssb_account_id_from=NULL;

                                            $transction_no = NULL;

                                            $transction_bank_from = NULL;

                                            $transction_bank_ac_from = NULL;

                                            $transction_bank_ifsc_from = NULL;

                                            $transction_bank_branch_from = NULL;

                                            $transction_bank_to = NULL;

                                            $transction_bank_ac_to = NULL;

                                        }elseif($request->bankTransferMode == 1){

                                            $payment_type = 2;

                                            $amount_from_id =$member->id;

                                            $amount_from_name = getMemberData($member->id)->first_name.' '.getMemberData($member->id)->last_name;

                                            $cheque_no = NULL;

                                            $cheque_date = NULL;

                                            $cheque_bank_from = NULL;

                                            $cheque_bank_ac_from = NULL;

                                            $cheque_bank_ifsc_from = NULL;

                                            $cheque_bank_branch_from = NULL;

                                            $cheque_bank_to = NULL;

                                            $cheque_bank_ac_to = NULL;

                                            $transction_no = $request->utrTransactionNumber;

                                            $v_no=NULL;

                                            $v_date=NULL;

                                            $ssb_account_id_from=NULL;

                                            $transction_bank_from = $request->customerBankName;

                                            $transction_bank_ac_from = $request->customerBankAccountNumber;

                                            $transction_bank_ifsc_from = $request->customerIfscCode;

                                            $transction_bank_branch_from = $request->customerBranchName;

                                            $transction_bank_to = $request->companyBank;

                                            $transction_bank_ac_to = $request->bankAccountNumber;

                                        }



                                        $penaltyDayBookRef = CommanController::createBranchDayBookReference($penaltyAmount);



                                        $roiallTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,3,12,33,$head4=NULL,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',0,'INR',$request->bankAccountNumber,getSamraddhBank($request->bankAccountNumber)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);                    
                                        $allTransaction = CommanController::createAllTransaction($penaltyDayBookRef,$mLoan->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,27,getSamraddhBankAccount($request->bankAccountNumber)->account_head_id,$head5=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$branch_id_to=NULL,$branch_id_from=NULL,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $principalmemberTransaction = CommanController::createMemberTransaction($penaltyDayBookRef,5,53,$loanId,$createDayBook,$member->id,$mLoan->applicant_id,$mLoan->branch_id,$bank_id=NULL,$account_id=NULL,$penaltyAmount,'Loan Panelty Charge','DR',0,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $samraddhBankDaybook = CommanController::createSamraddhBankDaybook($penaltyDayBookRef,$bank_id=NULL,$account_id=NULL,5,53,$loanId,$createDayBook,$member->id,$member_id=NULL,$mLoan->branch_id,$penaltyAmount,$penaltyAmount,$penaltyAmount,'Loan Panelty Charge','Cash A/C Cr. '.$penaltyAmount.'','Cash A/C Cr. '.$penaltyAmount.'','CR',$payment_type,'INR',$request->companyBank,getSamraddhBank($request->companyBank)->bank_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$amount_from_name,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,$member->id,$created_at=NULL);



                                        $createPrincipleBankClosing = CommanController::updateBackDateloanBankBalance($penaltyAmount,$request->companyBank,getSamraddhBankAccount($request->bankAccountNumber)->id,date("Y-m-d H:i:s", strtotime(convertDate($deposiDate))),0,0);

                                    }

                                    /************* Head Implement ****************/

                                    if($loanEmiPaymentMode == 0){

                                        $paymentMode = 4;

                                        $cheque_dd_no = NULL;

                                        $ssbpaymentMode=5;

                                        $online_payment_id = NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }elseif($loanEmiPaymentMode == 1){

                                        if($request->bankTransferMode == 0){

                                            $paymentMode = 1;

                                            $cheque_dd_no = $request->customerCheque;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = NULL;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }elseif($request->bankTransferMode == 1){

                                            $paymentMode = 2;

                                            $cheque_dd_no = NULL;

                                            $ssbpaymentMode=5;

                                            $online_payment_id = $request->utrTransactionNumber;

                                            $online_payment_by=NULL;

                                            $satRefId = NULL;

                                            $bank_name=NULL;

                                            $cheque_date=NULL;

                                            $account_number=NULL;

                                            $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                        }

                                    }elseif($loanEmiPaymentMode == 2){

                                        $cheque_dd_no=NULL;

                                        $paymentMode=0;

                                        $ssbpaymentMode=0;

                                        $online_payment_id=NULL;

                                        $online_payment_by=NULL;

                                        $satRefId = NULL;

                                        $bank_name=NULL;

                                        $cheque_date=NULL;

                                        $account_number=NULL;

                                        $paymentDate = date("Y-m-d ".$entryTime."", strtotime(convertDate($deposiDate)));;

                                    }



                                    $createDayBook = $this->createLoanDayBook($mLoan->loan_type,1,$mLoan->id,$lId=NULL,$mLoan->account_number,$mLoan->applicant_id,$penaltyAmount,$penaltyAmount,$opening_balance=NULL,$penaltyAmount,'Loan EMI penalty',$mLoan->branch_id,getBranchCode($mLoan->branch_id)->branch_code,'CR','INR',$paymentMode,$cheque_dd_no,$bank_name,$branch_name=NULL,$paymentDate,$online_payment_id,1,1,$cheque_date,$account_number,NULL,$member->first_name.' '.$member->last_name,$member->id,$mLoan->branch_id,$member->id,1);  

                                }

                                $status = "Success";
                                $code = 200;
                                $messages = 'Successfully pay group loan emi!';
                                $result = '';
                                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                            }
                        }else{
                            $status = "Error";
                            $code = 201;
                            $messages = 'Something went wrong!';
                            $result = '';
                            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                        }

                    }else{

                        $status = "Error";
                        $code = 201;
                        $messages = 'API token mismatch!';
                        $result = '';
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }
                }else{

                    $status = "Error";
                    $code = 201;
                    $messages = 'Something went wrong!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } 

            } catch (Exception $e) {
                $status = "Error";
                $code = 500;
                $messages = $e->getMessage();
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);

            }
        /*DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 
            $status = "Error";
            $code = 500;
            $messages = $ex->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);

        }*/
    }

    public function updateSsbDayBookAmount($amount,$account_number,$date)

    {

        $globaldate = $date;

        $entryTime = date("h:i:s");

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));



        $getCurrentBranchRecord = SavingAccountTranscation::where('account_no',$account_number)->whereDate('created_at',$entryDate)->first();

        $bResult = SavingAccountTranscation::find($getCurrentBranchRecord->id);

        $bData['opening_balance']=$getCurrentBranchRecord->opening_balance-$amount; 

        $bData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($globaldate)));

        $bResult->update($bData);



        $getNextBranchRecord = SavingAccountTranscation::where('account_no',$account_number)->whereDate('created_at','>',$entryDate)->orderby('created_at','ASC')->get();



        if($getNextBranchRecord){

            foreach ($getNextBranchRecord as $key => $value) {

                $sResult = SavingAccountTranscation::find($value->id);

                $sData['opening_balance'] = $value->opening_balance-$amount; 

                $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($globaldate)));

                $sResult->update($sData);

            }

        }

    }

    public static function createDayBook($transaction_id,$satRefId,$transaction_type,$account_id,$associateId,$memberId,$openingBalance,$deposite,$withdrawal,$description,$referenceno,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$account_no,$cheque_dd_no,$bank_name,$branch_name,$payment_date,$online_payment_id,$online_payment_by,$saving_account_id,$payment_type,$app_login_user_id,$is_app)
    { 
        $entryTime = date("h:i:s");
        $globaldate = Session::get('created_at');        
        foreach ($amountArray as $key=>$option) 
        {
            $loanTypeArray = array(3,5,6,8,9,10,11,12);
            $investmentTypeArray = array(3,5,6,8,9,10,11,12);
            $data_log['transaction_type']=$transaction_type;
            $data_log['transaction_id']=$transaction_id;
            $data_log['saving_account_transaction_reference_id']=$satRefId;
            if(in_array($transaction_type, $loanTypeArray)){
                $data_log['loan_id']=$account_id;
            }elseif($transaction_type == 13){
                $data_log['rent_id']=$account_id;
            }else{
                $data_log['investment_id']=$account_id;
            }
            $data_log['account_no']=$account_no;
            $data_log['associate_id']=$associateId;
            $data_log['member_id']=$memberId;
            $data_log['opening_balance']=$openingBalance;
            $data_log['deposit']=$deposite;
            $data_log['withdrawal']=$withdrawal;
            $data_log['description']=$description;
            $data_log['reference_no']=$referenceno;
            $data_log['branch_id']=$branch_id;
            $data_log['branch_code']=$branchCode;
            $data_log['amount']=$option;
            $data_log['currency_code']='INR';
            $data_log['payment_mode']=$payment_mode;
            $data_log['payment_type']=$payment_type;
            if($payment_mode==1 || $payment_mode==2)
            {
                $data_log['cheque_dd_no']=$cheque_dd_no;
                $data_log['bank_name']=$bank_name;
                $data_log['branch_name']=$branch_name;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d h:i:s", strtotime($payment_date));
                }
            }
            if($payment_mode==3)
            {
                $data_log['online_payment_id']=$online_payment_id;
                $data_log['online_payment_by']=$online_payment_by; 
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d h:i:s", strtotime($payment_date));
                }
            }
            if($payment_mode==4)
            {
                $data_log['saving_account_id']=$saving_account_id;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d h:i:s", strtotime($payment_date));
                }          
            } 
            $data_log['amount_deposit_by_name']=$deposit_by_name;
            $data_log['amount_deposit_by_id']=$deposit_by_id;
            $data_log['created_by_id']=1;
            $data_log['created_by']=2;
            //$data_log['created_at']=date("Y-m-d h:i:s", strtotime($payment_date));
            if($transaction_type == 16){
                $data_log['created_at']=date("Y-m-d", strtotime(convertDate($globaldate)));
            }else{
                $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($globaldate)));    
            }
            $data_log['app_login_user_id']=$app_login_user_id;
            $data_log['is_app']=$is_app;
            $transcation = Daybook::create($data_log);
            $tran_id = $transcation->id;
        }return $tran_id;
    }

    public static function createLoanDayBook($loan_type,$loan_sub_type,$loan_id,$group_loan_id,$account_number,$applicant_id,$roi_amount,$principal_amount,$opening_balance,$deposit,$description,$branch_id,$branch_code,$payment_type,$currency_code,$payment_mode,$cheque_dd_no,$bank_name,$branch_name,$payment_date,$online_payment_id,$created_by,$status,$cheque_date,$bank_account_number,$online_payment_by,$amount_deposit_by_name,$associate_id,$amount_deposit_by_id,$app_login_user_id,$is_app)
    { 
        $globaldate = Session::get('created_at');
        
        $data['loan_type']=$loan_type;
        $data['loan_sub_type']=$loan_sub_type;
        $data['loan_id']=$loan_id;
        $data['group_loan_id']=$group_loan_id;
        $data['account_number']=$account_number;
        $data['applicant_id']=$applicant_id;
        $data['associate_id']=$associate_id;
        $data['roi_amount']=$roi_amount;
        $data['principal_amount']=$principal_amount;
        $data['opening_balance']=$opening_balance;
        $data['deposit']=$deposit;
        $data['description']=$description;
        $data['branch_id']=$branch_id;
        $data['branch_code']=$branch_code;
        $data['payment_type']=$payment_type;
        $data['currency_code']=$currency_code;
        $data['payment_mode']=$payment_mode;
        $data['payment_date']=date("Y-m-d", strtotime(convertDate($payment_date)));
        $data['created_by']=$created_by;
        $data['status']=$status;
        $data['created_at']=$globaldate;

        if($payment_mode==1 || $payment_mode==2)
        {
            $data['cheque_dd_id']=$cheque_dd_no;
            $data['cheque_date']=date("Y-m-d", strtotime(convertDate($cheque_date)));
            $data['bank_id']=$bank_name;
            $data['bank_account_number']=$bank_account_number;
            $data['branch_name']=$branch_name;
        }
        if($payment_mode==3)
        {
            $data['online_payment_id']=$online_payment_id;
        }
        $data['online_payment_by']=$online_payment_by;
        $data['amount_deposit_by_name']=$amount_deposit_by_name;
        $data['amount_deposit_by_id']=$amount_deposit_by_id;
        $data['app_login_user_id']=$app_login_user_id;
        $data['is_app']=$is_app;
        $loadDayBook = LoanDayBooks::create($data);
        $loaddaybook_id = $loadDayBook->id;
        return $loaddaybook_id;
    }

    public function loanAmountDetails(Request $request)
    {   
        $associate_no = $request->associate_no; 
        $loanId = $request->loanId; 
        try { 

           $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();

            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){

                    $result=Memberloans::with('loanMember','loanMemberAssociate','loanBranch')->where('id',$loanId)->first();

                    $data['loanId'] = $loanId;
                    $data['emiAmount'] = $result->emi_amount;

                    $ssbAmount = getMemberSsbAccountDetail($result['loanMember']->id);
                    if($ssbAmount){
                        $ssbBalance = $ssbAmount['balance'];
                        $ssbId = $ssbAmount['id'];
                    }else{
                        $ssbBalance = 0;
                        $ssbId = 0;
                    }

                    $data['ssbBalance'] = $ssbBalance;
                    $data['ssbId'] = $ssbId;
                    $data['recoveredAmount'] = loanOutsandingAmount($result->id);
                    $data['lastRecoveredAmount'] = lastLoanRecoveredAmount($result->id,'loan_id');

                    if($result->emi_option == 1){

                        $closingAmountROI = $result->due_amount*$result->ROI/1200;  

                    }elseif($result->emi_option == 2){

                        $closingAmountROI = $result->due_amount*$result->ROI/5200;

                    }elseif($result->emi_option == 3){

                        $closingAmountROI = $result->due_amount*$result->ROI/36500;

                    }

                    $closingAmount = round($result->due_amount+$closingAmountROI);

                    $data['closingAmount'] = $closingAmount;

                    if($result->emi_option == 1){

                        $loanComplateDate = date('Y-m-d');

                        $dueStartDate = $result->approve_date;   

                        $dts1 =strtotime($dueStartDate); 

                        $dts2 = strtotime($loanComplateDate); 

                        $dyear1 = date('Y', $dts1); 

                        $dyear2 = date('Y', $dts2); 

                        $dmonth1 = date('m', $dts1); $dmonth2 = date('m', $dts2); 

                        $dueTime = (($dyear2 - $dyear1) * 12) + ($dmonth2 - $dmonth1) ;

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 2){

                        $loanStartDate = $result->approve_date;

                        $startDate=date("m/d/Y", strtotime(convertDate($loanStartDate)));

                        $endDate = date('m/d/Y');

                        $first = DateTime::createFromFormat('m/d/Y', $startDate);

                        $second = DateTime::createFromFormat('m/d/Y', $endDate);

                        $dueTime = floor($first->diff($second)->days/7);

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 3){

                        $startDate = strtotime($result->approve_date);

                        $endDate = strtotime(date('Y-m-d'));

                        $datediff = $endDate - $startDate;

                        $dueTime = round($datediff / (60 * 60 * 24));

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 4){

                        $dueAmount = 0;

                    }

                    $data['dueAmount'] = $dueAmount;

                    if(date('Y-m-d') > $result->closing_date){

                        if($result->emi_option == 1){

                            $loanStartDate = $result->closing_date;

                            $loanComplateDate = date('Y-m-d');

                            $ts1 =strtotime($loanStartDate); 

                            $ts2 = strtotime($loanComplateDate); 

                            $year1 = date('Y', $ts1); 

                            $year2 = date('Y', $ts2); 

                            $month1 = date('m', $ts1); $month2 = date('m', $ts2); 

                            $penaltyTime = (($year2 - $year1) * 12) + ($month2 - $month1) ;

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);  

                        }elseif($result->emi_option == 2){

                            $loanStartDate = $result->closing_date;

                            $startDate=date("m/d/Y", strtotime(convertDate($loanStartDate)));

                            $endDate = date('m/d/Y');

                            $first = DateTime::createFromFormat('m/d/Y', $startDate);

                            $second = DateTime::createFromFormat('m/d/Y', $endDate);

                            $penaltyTime = floor($first->diff($second)->days/7);

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);

                        }elseif($result->emi_option == 3){

                            $startDate = strtotime($result->closing_date);

                            $endDate = strtotime(date('Y-m-d'));

                            $datediff = $endDate - $startDate;

                            $penaltyTime = round($datediff / (60 * 60 * 24));

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);

                        }elseif($result->emi_option == 4){

                            $penaltyTime = '';

                            $penaltyAmount = '';

                        }        

                    }else{

                        $penaltyTime = '';

                        $penaltyAmount = '';

                    }

                    $data['penaltyAmount'] = $penaltyAmount;
                    $data['penaltyTime'] = $penaltyAmount;

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Associate details!';
                    $result   = $data;
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }else{

                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            }else{

                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            } 

        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);

        }
    }

    public function groupLoanAmountDetails(Request $request)
    {   
        $associate_no = $request->associate_no; 
        $loanId = $request->loanId; 
        try { 

           $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();

            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){

                    $result=Grouploans::with('loanMember','loanMemberAssociate','gloanBranch')->where('id',$loanId)->first();
                    
                    $data['loanId'] = $loanId;
                    $data['emiAmount'] = $result->emi_amount;

                    $ssbAmount = getMemberSsbAccountDetail($result['loanMember']->id);
                    if($ssbAmount){
                        $ssbBalance = $ssbAmount['balance'];
                        $ssbId = $ssbAmount['id'];
                    }else{
                        $ssbBalance = 0;
                        $ssbId = 0;
                    }

                    $data['ssbBalance'] = $ssbBalance;
                    $data['ssbId'] = $ssbId;
                    $data['recoveredAmount'] = loanOutsandingAmount($result->id);
                    $data['lastRecoveredAmount'] = lastLoanRecoveredAmount($result->id,'loan_id');

                    if($result->emi_option == 1){

                        $closingAmountROI = $result->due_amount*$result->ROI/1200;  

                    }elseif($result->emi_option == 2){

                        $closingAmountROI = $result->due_amount*$result->ROI/5200;

                    }elseif($result->emi_option == 3){

                        $closingAmountROI = $result->due_amount*$result->ROI/36500;

                    }

                    $closingAmount = round($result->due_amount+$closingAmountROI);

                    $data['closingAmount'] = $closingAmount;

                    if($result->emi_option == 1){

                        $loanComplateDate = date('Y-m-d');

                        $dueStartDate = $result->approve_date;   

                        $dts1 =strtotime($dueStartDate); 

                        $dts2 = strtotime($loanComplateDate); 

                        $dyear1 = date('Y', $dts1); 

                        $dyear2 = date('Y', $dts2); 

                        $dmonth1 = date('m', $dts1); $dmonth2 = date('m', $dts2); 

                        $dueTime = (($dyear2 - $dyear1) * 12) + ($dmonth2 - $dmonth1) ;

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 2){

                        $loanStartDate = $result->approve_date;

                        $startDate=date("m/d/Y", strtotime(convertDate($loanStartDate)));

                        $endDate = date('m/d/Y');

                        $first = DateTime::createFromFormat('m/d/Y', $startDate);

                        $second = DateTime::createFromFormat('m/d/Y', $endDate);

                        $dueTime = floor($first->diff($second)->days/7);

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 3){

                        $startDate = strtotime($result->approve_date);

                        $endDate = strtotime(date('Y-m-d'));

                        $datediff = $endDate - $startDate;

                        $dueTime = round($datediff / (60 * 60 * 24));

                        $cAmount = round($dueTime*$result->emi_amount);  

                        $dueAmount = round($cAmount-$result->received_emi_amount);

                    }elseif($result->emi_option == 4){

                        $dueAmount = 0;

                    }

                    $data['dueAmount'] = $dueAmount;

                    if(date('Y-m-d') > $result->closing_date){

                        if($result->emi_option == 1){

                            $loanStartDate = $result->closing_date;

                            $loanComplateDate = date('Y-m-d');

                            $ts1 =strtotime($loanStartDate); 

                            $ts2 = strtotime($loanComplateDate); 

                            $year1 = date('Y', $ts1); 

                            $year2 = date('Y', $ts2); 

                            $month1 = date('m', $ts1); $month2 = date('m', $ts2); 

                            $penaltyTime = (($year2 - $year1) * 12) + ($month2 - $month1) ;

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);  

                        }elseif($result->emi_option == 2){

                            $loanStartDate = $result->closing_date;

                            $startDate=date("m/d/Y", strtotime(convertDate($loanStartDate)));

                            $endDate = date('m/d/Y');

                            $first = DateTime::createFromFormat('m/d/Y', $startDate);

                            $second = DateTime::createFromFormat('m/d/Y', $endDate);

                            $penaltyTime = floor($first->diff($second)->days/7);

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);

                        }elseif($result->emi_option == 3){

                            $startDate = strtotime($result->closing_date);

                            $endDate = strtotime(date('Y-m-d'));

                            $datediff = $endDate - $startDate;

                            $penaltyTime = round($datediff / (60 * 60 * 24));

                            $penaltyAmount = round($penaltyTime*$closingAmountROI);

                        }elseif($result->emi_option == 4){

                            $penaltyTime = '';

                            $penaltyAmount = '';

                        }        

                    }else{

                        $penaltyTime = '';

                        $penaltyAmount = '';

                    }

                    $data['penaltyAmount'] = $penaltyAmount;
                    $data['penaltyTime'] = $penaltyAmount;

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Associate details!';
                    $result   = $data;
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }else{

                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            }else{

                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            } 

        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);

        }
    }



    public function loanRecovery(Request $request)
    {   

        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->page>0 && $request->length>0)
                    {
                        $data = array();
                        $loan_data =  LoanDayBooks::where('loan_type','!=',3)->where('app_login_user_id',$member->id)->where('is_app',1);  

                        $loan_data1=$loan_data->orderby('created_at','DESC')->get();
                        $count=count($loan_data1);
                        if($request->page==1)
                        {
                            $start=0;
                        }
                        else
                        {
                            $start=$request->page*$request->length;
                        }
                        $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();
                        foreach ($loan_data as $key => $row) 
                        {
                            $row_loan=Memberloans::with('loanMember','loanMemberAssociate','loanBranch')->where('loan_type','!=',3)->where('id',$row->loan_id)->first();
                           // print_r($row_loan->loanMember);die;
                            $data[$key]['branch_name'] = $row_loan->loanBranch->name;
                            $data[$key]['branch_code'] = $row_loan->loanBranch->branch_code;
                            $data[$key]['account_number'] = $row_loan->account_number;
                            if($row_loan->loan_type == 1){
                                $plan_name = 'Personal Loan';
                            }elseif($row_loan->loan_type == 2){
                                $plan_name = 'Staff Loan(SL)';
                            }elseif($row_loan->loan_type == 3){
                                $plan_name = 'Group Loan';    
                            }elseif($row_loan->loan_type == 4){
                                $plan_name = 'Loan against Investment plan(DL) ';   
                            }

                            $data[$key]['loan_type'] = $plan_name;
                            $member_name = $row_loan->loanMember->first_name.' '.$row_loan->loanMember->last_name;
                            $member_id =  $row_loan->loanMember->member_id;
                            $associate_name = $row_loan->loanMemberAssociate->first_name.' '.$row_loan->loanMemberAssociate->last_name;
                            $associate_id =  $row_loan->loanMemberAssociate->associate_no;

                            $data[$key]['member_name'] = $member_name;
                            $data[$key]['member_id'] = $member_id;
                            $data[$key]['associate_name'] = $associate_name;
                            $data[$key]['associate_id'] = $associate_id;


                            $data[$key]['payment_date'] = date("d/m/Y", strtotime( $row->payment_date));
                            $paymentMode = 'N/A';
                            if($row->payment_mode == 0){
                                $paymentMode = 'Cash';
                            }elseif($row->payment_mode == 1){
                                $paymentMode = 'Cheque';
                            }elseif($row->payment_mode == 2){
                                $paymentMode = 'DD';
                            }elseif($row->payment_mode == 3){
                                $paymentMode = 'Online Transaction';
                            }elseif($row->payment_mode == 4){
                                $paymentMode = 'By Saving Account ';
                            }
                            $data[$key]['payment_mode'] =  $paymentMode;
                            $data[$key]['description'] = $row->description;
                            if($row->loan_sub_type == 1){
                                $penalty =  $row->deposit; 
                            }else{
                                $penalty =  'N/A';
                            }
                            $data[$key]['penalty'] = $penalty;

                            if($row->loan_sub_type == 0){
                                $deposite =  $row->deposit; 
                            }else{
                                $deposite =  'N/A';
                            }
                            $data[$key]['deposite'] = $deposite;

                            if($row->loan_sub_type == 0){
                                $roi_amount =  $row->roi_amount; 
                            }else{
                                $roi_amount =  'N/A';
                            }
                            $data[$key]['roi_amount'] = $roi_amount;

                            if($row->loan_sub_type == 0){
                                $principal_amount =  $row->principal_amount; 
                            }else{
                                $principal_amount =  'N/A';
                            }
                            $data[$key]['principal_amount'] = $principal_amount;

                            if($row->loan_sub_type == 0){
                                $opening_balance =  $row->opening_balance; 
                            }else{
                                $opening_balance =  'N/A';
                            }
                            $data[$key]['opening_balance'] = $opening_balance;


                                              

                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Loan Recovery!';
                    $page  = $request->page;
                    $length  = $request->length;
                    $result   = ['loan_recovery' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];
                    $associate_status=$member->associate_app_status;                  

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }
                    else
                    {

                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }                   

                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }

    public function groupLoanRecovery(Request $request)
    {   

        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->page>0 && $request->length>0)
                    {
                        $data = array();
                        $loan_data =  LoanDayBooks::where('loan_type','=',3)->where('app_login_user_id',$member->id)->where('is_app',1);  

                        $loan_data1=$loan_data->orderby('created_at','DESC')->get();
                        $count=count($loan_data1);
                        if($request->page==1)
                        {
                            $start=0;
                        }
                        else
                        {
                            $start=$request->page*$request->length;
                        }
                        $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();
                        foreach ($loan_data as $key => $row) 
                        {
                            $row_loan=Grouploans::with('loanMember','loanMemberAssociate','gloanBranch')->where('id',$row->loan_id)->first();
                           // print_r($row_loan->loanMember);die;
                            $data[$key]['branch_name'] = $row_loan->gloanBranch->name;
                            $data[$key]['branch_code'] = $row_loan->gloanBranch->branch_code;
                            $data[$key]['account_number'] = $row_loan->account_number;
                             
                                $plan_name = 'Group Loan';    
                             

                            $data[$key]['loan_type'] = $plan_name;
                            $member_name = $row_loan->loanMember->first_name.' '.$row_loan->loanMember->last_name;
                            $member_id =  $row_loan->loanMember->member_id;
                            $associate_name = $row_loan->loanMemberAssociate->first_name.' '.$row_loan->loanMemberAssociate->last_name;
                            $associate_id =  $row_loan->loanMemberAssociate->associate_no;

                            $data[$key]['member_name'] = $member_name;
                            $data[$key]['member_id'] = $member_id;
                            $data[$key]['associate_name'] = $associate_name;
                            $data[$key]['associate_id'] = $associate_id;


                            $data[$key]['payment_date'] = date("d/m/Y", strtotime( $row->payment_date));
                            if($row->payment_mode == 0){
                                $paymentMode = 'Cash';
                            }elseif($row->payment_mode == 1){
                                $paymentMode = 'Cheque';
                            }elseif($row->payment_mode == 2){
                                $paymentMode = 'DD';
                            }elseif($row->payment_mode == 3){
                                $paymentMode = 'Online Transaction';
                            }elseif($row->payment_mode == 4){
                                $paymentMode = 'By Saving Account ';
                            }
                            $data[$key]['payment_mode'] =  $paymentMode;
                            $data[$key]['description'] = $row->description;
                            if($row->loan_sub_type == 1){
                                $penalty =  $row->deposit; 
                            }else{
                                $penalty =  'N/A';
                            }
                            $data[$key]['penalty'] = $penalty;

                            if($row->loan_sub_type == 0){
                                $deposite =  $row->deposit; 
                            }else{
                                $deposite =  'N/A';
                            }
                            $data[$key]['deposite'] = $deposite;

                            if($row->loan_sub_type == 0){
                                $roi_amount =  $row->roi_amount; 
                            }else{
                                $roi_amount =  'N/A';
                            }
                            $data[$key]['roi_amount'] = $roi_amount;

                            if($row->loan_sub_type == 0){
                                $principal_amount =  $row->principal_amount; 
                            }else{
                                $principal_amount =  'N/A';
                            }
                            $data[$key]['principal_amount'] = $principal_amount;

                            if($row->loan_sub_type == 0){
                                $opening_balance =  $row->opening_balance; 
                            }else{
                                $opening_balance =  'N/A';
                            }
                            $data[$key]['opening_balance'] = $opening_balance;


                                              

                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Group Loan Recovery!';
                    $page  = $request->page;
                    $length  = $request->length;
                    $result   = ['group_loan_recovery' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];
                    $associate_status=$member->associate_app_status;                  

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }
                    else
                    {

                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }                   

                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }



    public function loanRecoveryList(Request $request)
    {   

        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->page>0 && $request->length>0)
                    {
                        $data = array();
                        $loan_data =  Memberloans::with('loanMember','loanMemberAssociate','loanBranch')->where('status','!=',0)->where('loan_type','!=',3);  

                        $loan_data1=$loan_data->orderby('created_at','DESC')->get();
                        $count=count($loan_data1);
                        if($request->page==1)
                        {
                            $start=0;
                        }
                        else
                        {
                            $start=$request->page*$request->length;
                        }
                        $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();
                        foreach ($loan_data as $key => $row) 
                        {
                            
                           // print_r($row->loanMember);die;
                            $member_name = $row->loanMember->first_name.' '.$row->loanMember->last_name;
                            $member_id =  $row->loanMember->member_id;
                            $associate_name = $row->loanMemberAssociate->first_name.' '.$row->loanMemberAssociate->last_name;
                            $associate_id =  $row->loanMemberAssociate->associate_no;
                            $data[$key]['id'] = $row->id;
                            $data[$key]['branch_name'] = $row->loanBranch->name;
                            $data[$key]['branch_code'] = $row->loanBranch->branch_code;
                            $data[$key]['sector_name'] = $row->loanBranch->sector;
                            $data[$key]['region_name'] = $row->loanBranch->regan;
                            $data[$key]['zone_name'] = $row->loanBranch->zone;
                            $data[$key]['account_number'] = $row->account_number;
                            $data[$key]['member_name'] = $member_name;
                            $data[$key]['member_id'] = $member_id;
                            if($row->loan_type == 1){
                                $plan_name = 'Personal Loan';
                            }elseif($row->loan_type == 2){
                                $plan_name = 'Staff Loan(SL)';
                            }elseif($row->loan_type == 3){
                                $plan_name = 'Group Loan';    
                            }elseif($row->loan_type == 4){
                                $plan_name = 'Loan against Investment plan(DL) ';   
                            }

                            $data[$key]['loan_type'] = $plan_name;                           
                            
                            if($row->emi_option == 1){
                                $tenure =  $row->emi_period.' Months';
                            }elseif ($row->emi_option == 2) {
                                $tenure =  $row->emi_period.' Weeks';
                            }elseif ($row->emi_option == 3) {
                                $tenure =  $row->emi_period.' Days';
                            }
                            $data[$key]['tenure'] = $tenure;
                            if($row->file_charges){
                                $file_charge =  $row->file_charges;
                            }
                            else{
                                $file_charge = 'N/A'; 
                            }
                            $data[$key]['transfer_amount'] = $row->deposite_amount;

                            $data[$key]['file_charge'] = $file_charge;

                            if(fileChargePaymentMode($row->id,6)){
                                $file_charges_payment_mode =  fileChargePaymentMode($row->id,6);
                            }
                            else{
                                $file_charges_payment_mode = 'N/A'; 
                            }
                            $data[$key]['file_charges_payment_mode'] = $file_charges_payment_mode;
                            $data[$key]['loan_amount'] = $row->amount;
                            $data[$key]['outstanding_amount'] = $row->due_amount;
                            $data[$key]['last_recovery_date'] = date("d/m/Y", strtotime( $row->closing_date));
                            $data[$key]['associate_code'] = $associate_id;
                            $data[$key]['associate_name'] = $associate_name;
                            if(loanOutsandingAmount($row->id)){
                                $total_payment =  loanOutsandingAmount($row->id);
                            }else{
                                $total_payment = 'N/A';
                            }
                            $data[$key]['total_payment'] = $total_payment;
                            if($row['approve_date']){
                                $approve_date = date("d/m/Y", strtotime( $row['approve_date']));
                            }else{
                                $approve_date = 'N/A';
                            }
                            $data[$key]['approve_date'] = $approve_date;
                            $data[$key]['application_date'] = date("d/m/Y", strtotime( $row['created_at']));
                            if($row->status == 0){
                                $status = 'Pending';
                            }else if($row->status == 1){
                                $status = 'Approved';
                            }else if($row->status == 2){
                                $status = 'Rejected';
                            }else if($row->status == 3){
                                $status = 'Clear';
                            }else if($row->status == 4){
                                $status = 'Due';
                            }
                            $data[$key]['status'] = $status;                         

                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Loan Recovery List!';
                    $page  = $request->page;
                    $length  = $request->length;
                    $result   = ['loan_recovery' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];
                    $associate_status=$member->associate_app_status;                  

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }
                    else
                    {

                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }                   

                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }


    public function groupLoanRecoveryList(Request $request)
    {   

        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->page>0 && $request->length>0)
                    {
                        $data = array();
                        $loan_data =  Grouploans::with('loanMember','loanMemberAssociate','gloanBranch')->where('status','!=',0);  

                        $loan_data1=$loan_data->orderby('created_at','DESC')->get();
                        $count=count($loan_data1);
                        if($request->page==1)
                        {
                            $start=0;
                        }
                        else
                        {
                            $start=$request->page*$request->length;
                        }
                        $loan_data=$loan_data->orderby('created_at','DESC')->offset($start)->limit($request->length)->get();
                        foreach ($loan_data as $key => $row) 
                        {
                            $member_name = $row->loanMember->first_name.' '.$row->loanMember->last_name;
                            $member_id =  $row->loanMember->member_id;
                            $associate_name = $row->loanMemberAssociate->first_name.' '.$row->loanMemberAssociate->last_name;
                            $associate_id =  $row->loanMemberAssociate->associate_no;
                            $data[$key]['id'] = $row->id;
                            $data[$key]['branch_name'] = $row->gloanBranch->name;
                            $data[$key]['branch_code'] = $row->gloanBranch->branch_code;
                            $data[$key]['sector_name'] = $row->gloanBranch->sector;
                            $data[$key]['region_name'] = $row->gloanBranch->regan;
                            $data[$key]['zone_name'] = $row->gloanBranch->zone;
                            $data[$key]['group_loan_id'] = $row->group_loan_common_id;
                            $data[$key]['account_number'] = $row->account_number;
                            $data[$key]['member_name'] = $member_name;
                            $data[$key]['member_id'] = $member_id;
                            $plan_name = 'Group Loan'; 

                            $data[$key]['loan_type'] = $plan_name;                           
                            
                            if($row->emi_option == 1){
                                $tenure =  $row->emi_period.' Months';
                            }elseif ($row->emi_option == 2) {
                                $tenure =  $row->emi_period.' Weeks';
                            }elseif ($row->emi_option == 3) {
                                $tenure =  $row->emi_period.' Days';
                            }
                            $data[$key]['tenure'] = $tenure;
                            if($row->file_charges){
                                $file_charge =  $row->file_charges;
                            }
                            else{
                                $file_charge = 'N/A'; 
                            }
                            $data[$key]['transfer_amount'] = $row->deposite_amount;
                            $data[$key]['file_charge'] = $file_charge;

                            if(fileChargePaymentMode($row->id,10)){
                                $file_charges_payment_mode =  fileChargePaymentMode($row->id,10);
                            }
                            else{
                                $file_charges_payment_mode = 'N/A'; 
                            }
                            $data[$key]['file_charges_payment_mode'] = $file_charges_payment_mode;
                            $data[$key]['loan_amount'] = $row->amount;
                            $data[$key]['outstanding_amount'] = $row->due_amount;
                            $data[$key]['last_recovery_date'] = date("d/m/Y", strtotime( $row->closing_date));
                            $data[$key]['associate_code'] = $associate_id;
                            $data[$key]['associate_name'] = $associate_name;
                            if(loanOutsandingAmount($row->id)){
                                $total_payment =  loanGroupOutsandingAmount($row->id);
                            }else{
                                $total_payment = 'N/A';
                            }
                            $data[$key]['total_payment'] = $total_payment;
                            if($row['approve_date']){
                                $approve_date = date("d/m/Y", strtotime( $row['approve_date']));
                            }else{
                                $approve_date = 'N/A';
                            }
                            $data[$key]['approve_date'] = $approve_date;
                            $data[$key]['application_date'] = date("d/m/Y", strtotime( $row['created_at']));
                            if($row->status == 0){
                                $status = 'Pending';
                            }else if($row->status == 1){
                                $status = 'Approved';
                            }else if($row->status == 2){
                                $status = 'Rejected';
                            }else if($row->status == 3){
                                $status = 'Clear';
                            }else if($row->status == 4){
                                $status = 'Due';
                            }
                            $data[$key]['status'] = $status;


                                              

                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Group Loan Recovery List!';
                    $page  = $request->page;
                    $length  = $request->length;
                    $result   = ['group_loan_recovery' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length];
                    $associate_status=$member->associate_app_status;                  

                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }
                    else
                    {

                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }                   

                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }






    public function plLoanDetail(Request $request)
    {   

        $data = array();
                        $data['loan_details']['lable']='';
                        $data['loan_details']['type']=00; 
                        $data['loan_details']['loan_amount']='';
                        $data['loan_details']['EMI_mode_option']='';
                        $data['loan_details']['loan_purpose']='';
                        $data['loan_details']['associate_code']='';
                        $data['loan_details']['bank_acount']='';


                        $data['loan_details']['ifsc']='';
                        $data['loan_details']['bank_name']='';
                        $data['loan_details']['applicant_id']='';
                        $data['applicant']=array();
                        $data['co_applicant']=array();
                        $data['guarantor_applicant']=array();
                        $data['other_doc']=array();



        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->id>0 )
                    {

                        $id=$request->id;
                        $loan_data = $loanDetails= Memberloans::with('loan','LoanApplicants','LoanCoApplicants','LoanGuarantor','Loanotherdocs')->where('id',$id)->where('loan_type',1)->first();  
                        if($loan_data)
                        {  
                            $data['loan_details']['lable']='Personal Loan Details';
                            
                             $data['loan_details']['type']=$loanDetails->loan_type; 

                            $data['loan_details']['loan_amount']=$loanDetails->amount;
                            if($loanDetails->emi_option == 1)
                                $emi_mode=$loanDetails->emi_period.' Months';
                            elseif($loanDetails->emi_option == 2)
                                 $emi_mode=$loanDetails->emi_period.' Weeks';
                             elseif($loanDetails->emi_option == 3)
                                $emi_mode=$loanDetails->emi_period.' Days';

                            $data['loan_details']['EMI_mode_option']=$emi_mode;
                            $data['loan_details']['loan_purpose']=$loanDetails->loan_purpose;

                            $data['loan_details']['associate_code']=getAssociateId($loanDetails->associate_member_id);
                            $data['loan_details']['bank_acount']=$loanDetails->bank_account;
                            $data['loan_details']['ifsc']= $loanDetails->ifsc_code;
                            $data['loan_details']['bank_name']=$loanDetails->bank_name;
                            $data['loan_details']['applicant_id']=getApplicantid($loanDetails->applicant_id);

                            foreach($loanDetails['LoanApplicants'] as $key =>$LoanApplicant)
                            {
                                $aDetails = getMemberData($loanDetails->applicant_id);

                                $data['applicant'][$key]['details']['applicant_id']=$aDetails->member_id;
                                $data['applicant'][$key]['details']['name']=$aDetails->first_name.' '.$aDetails->last_name ;
                                $data['applicant'][$key]['details']['father_name']=$aDetails->father_husband;
                                $data['applicant'][$key]['details']['mobile_no']=$aDetails->mobile_no;
                                $data['applicant'][$key]['details']['email_id']=$aDetails->email;
                                $permanent_address='';
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanApplicant->occupation);
                                $data['applicant'][$key]['employment_details']['organization']=$LoanApplicant->organization;
                                $data['applicant'][$key]['employment_details']['designation']=$LoanApplicant->designation;
                                $data['applicant'][$key]['employment_details']['monthly_income']=$LoanApplicant->monthly_income;
                                $data['applicant'][$key]['employment_details']['year_from']=$LoanApplicant->year_from;

                                $data['applicant'][$key]['bank_detail']['bank_name']=$LoanApplicant->bank_name;
                                $data['applicant'][$key]['bank_detail']['bank_ac']=$LoanApplicant->bank_account_number;
                                $data['applicant'][$key]['bank_detail']['ifsc']=$LoanApplicant->ifsc_code;
                                $data['applicant'][$key]['bank_detail']['cheque1']=$LoanApplicant->cheque_number_1;
                                $data['applicant'][$key]['bank_detail']['cheque2']=$LoanApplicant->cheque_number_2;
                                if($LoanApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['applicant'][$key]['documents']['id_no']=$LoanApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanApplicant->id_proof_file_id);
                                $data['applicant'][$key]['documents']['upload_file']=array();

                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['applicant'][$key]['documents']['address_id_proof']=$LoanApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanApplicant->address_proof_file_id);
                                $data['applicant'][$key]['documents']['address_upload_file']=array();

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanApplicant->income_remark)
                                {
                                    $income_remark=$LoanApplicant->income_remark;
                                }

                                $data['applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanApplicant->income_file_id);
                                $data['applicant'][$key]['documents']['income_upload_file']=array();
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }  
                                if($LoanApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['applicant'][$key]['documents']['security']=$security;
                            }

                            // Co-applicant Details

                            foreach($loanDetails['LoanCoApplicants'] as $key =>$LoanCoApplicant)
                            {
                                
                                $caDetails = getMemberData($LoanCoApplicant->member_id);

                                $data['co_applicant'][$key]['details']['applicant_id']=$caDetails->member_id;
                                $data['co_applicant'][$key]['details']['name']=$caDetails->first_name.' '.$caDetails->last_name ;
                                $data['co_applicant'][$key]['details']['father_name']=$caDetails->father_husband;
                                $data['co_applicant'][$key]['details']['mobile_no']=$caDetails->mobile_no;
                                $data['co_applicant'][$key]['details']['email_id']=$caDetails->email;
                                $permanent_address='';
                                if($LoanCoApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanCoApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanCoApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanCoApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanCoApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanCoApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['co_applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['co_applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['co_applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanCoApplicant->occupation);
                                $data['co_applicant'][$key]['employment_details']['organization']=$LoanCoApplicant->organization;
                                $data['co_applicant'][$key]['employment_details']['designation']=$LoanCoApplicant->designation;
                                $data['co_applicant'][$key]['employment_details']['monthly_income']=$LoanCoApplicant->monthly_income;
                                $data['co_applicant'][$key]['employment_details']['year_from']=$LoanCoApplicant->year_from;

                                $data['co_applicant'][$key]['bank_detail']['bank_name']=$LoanCoApplicant->bank_name;
                                $data['co_applicant'][$key]['bank_detail']['bank_ac']=$LoanCoApplicant->bank_account_number;
                                $data['co_applicant'][$key]['bank_detail']['ifsc']=$LoanCoApplicant->ifsc_code;
                                $data['co_applicant'][$key]['bank_detail']['cheque1']=$LoanCoApplicant->cheque_number_1;
                                $data['co_applicant'][$key]['bank_detail']['cheque2']=$LoanCoApplicant->cheque_number_2;
                                if($LoanCoApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanCoApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanCoApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanCoApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanCoApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['co_applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['co_applicant'][$key]['documents']['id_no']=$LoanCoApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanCoApplicant->id_proof_file_id);
                                $data['co_applicant'][$key]['documents']['upload_file'] =array();

                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/id_proof/'.$applicantFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanCoApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanCoApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanCoApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanCoApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanCoApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanCoApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanCoApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanCoApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['co_applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['co_applicant'][$key]['documents']['address_id_proof']=$LoanCoApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanCoApplicant->address_proof_file_id);
                                $data['co_applicant'][$key]['documents']['address_upload_file'] =array(); 

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 

                                $coapplicantUnderDocFiles = getFileData($LoanCoApplicant->under_taking_doc);
                                $data['co_applicant'][$key]['documents']['under_taking'] =array();

                                if($coapplicantUnderDocFiles)
                                {
                                    foreach($coapplicantUnderDocFiles as $k1=> $coapplicantUnderDocFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['under_taking _doc'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['under_taking'][$k1]['name']=$coapplicantUnderDocFile->file_name ;
                                    }
                                } 


                                if($LoanCoApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanCoApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanCoApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['co_applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanCoApplicant->income_remark)
                                {
                                    $income_remark=$LoanCoApplicant->income_remark;
                                }
                                $data['co_applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanCoApplicant->income_file_id);
                                $data['co_applicant'][$key]['documents']['income_upload_file'] =array(); 
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['co_applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }  
                                if($LoanCoApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanCoApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanCoApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['co_applicant'][$key]['documents']['security']=$security;
                            }

                             // Guarantor Details
                            foreach($loanDetails['LoanGuarantor'] as $key=>$LoanGuarantor)
                            {
                                
                                $gaDetails = getMemberData($LoanGuarantor->member_id);
                                if($gaDetails ) 
                                { $a=$gaDetails->member_id ;
                                }else{ $a='N/A'; }

                                if($gaDetails ) 
                                { $email=$gaDetails->email ;
                                }else{ $email='N/A'; }

                                $data['guarantor_applicant'][$key]['details']['applicant_id']=$a;
                                $data['guarantor_applicant'][$key]['details']['name']=$LoanGuarantor->name ;
                                $data['guarantor_applicant'][$key]['details']['father_name']=$LoanGuarantor->father_name;
                                $data['guarantor_applicant'][$key]['details']['dob']=date("m/d/Y", strtotime(convertDate($LoanGuarantor->dob)));
                                $data['guarantor_applicant'][$key]['details']['email_id']=$email;
                                $permanent_address='';
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['guarantor_applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['guarantor_applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['guarantor_applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanGuarantor->occupation);
                                $data['guarantor_applicant'][$key]['employment_details']['organization']=$LoanGuarantor->organization;
                                $data['guarantor_applicant'][$key]['employment_details']['designation']=$LoanGuarantor->designation;
                                $data['guarantor_applicant'][$key]['employment_details']['monthly_income']=$LoanGuarantor->monthly_income;
                                $data['guarantor_applicant'][$key]['employment_details']['year_from']=$LoanGuarantor->year_from;

                                $data['guarantor_applicant'][$key]['bank_detail']['bank_name']=$LoanGuarantor->bank_name;
                                $data['guarantor_applicant'][$key]['bank_detail']['bank_ac']=$LoanGuarantor->bank_account_number;
                                $data['guarantor_applicant'][$key]['bank_detail']['ifsc']=$LoanGuarantor->ifsc_code;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque1']=$LoanGuarantor->cheque_number_1;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque2']=$LoanGuarantor->cheque_number_2;
                                if($LoanGuarantor->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanGuarantor->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanGuarantor->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanGuarantor->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanGuarantor->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['guarantor_applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['guarantor_applicant'][$key]['documents']['id_no']=$LoanGuarantor->id_proof_number;
                                $applicantFiles = getFileData($LoanGuarantor->id_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['upload_file'] =array(); 
                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/id_proof/'.$applicantFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                }  
                                if($LoanGuarantor->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanGuarantor->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanGuarantor->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanGuarantor->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanGuarantor->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanGuarantor->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanGuarantor->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanGuarantor->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['guarantor_applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['guarantor_applicant'][$key]['documents']['address_id_proof']=$LoanGuarantor->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanGuarantor->address_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['address_upload_file'] =array(); 
                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 

                                $coapplicantUnderDocFiles = getFileData($LoanGuarantor->under_taking_doc);
                                $data['guarantor_applicant'][$key]['documents']['under_taking_doc'] =array();

                                if($coapplicantUnderDocFiles)
                                {
                                    foreach($coapplicantUnderDocFiles as $k1=> $coapplicantUnderDocFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['name']=$coapplicantUnderDocFile->file_name ;
                                    }
                                } 


                                if($LoanGuarantor->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanGuarantor->income_type==1)
                                    $income='ITR';
                                elseif($LoanGuarantor->income_type==2)
                                    $income='Others ';  
                               
                                $data['guarantor_applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanGuarantor->income_remark)
                                {
                                    $income_remark=$LoanGuarantor->income_remark;
                                }
                                $data['guarantor_applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanGuarantor->income_file_id);
                                $data['guarantor_applicant'][$key]['documents']['income_upload_file'] =array(); 
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/income_proof/'.$applicantIncomeFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }   
                                if($LoanGuarantor->security==0)
                                    $security='Cheuqe';
                                elseif($LoanGuarantor->security==1)
                                    $security='Passbook';
                                elseif($LoanGuarantor->security==2)
                                    $security='FD Certificate'; 

                                $data['guarantor_applicant'][$key]['documents']['security']=$security;
                            }
                            $data['other_doc']=array();
                            if ( count( $loanDetails['Loanotherdocs'] ) > 0)
                            {
                                foreach($loanDetails['Loanotherdocs'] as $key=>$loanotherdocs)
                                { 

                                    $data['other_doc'][$key]['title']=$loanotherdocs['title'] ;

                                    $files = getFileData($loanotherdocs['file_id']);
                                    $data['other_doc'][$key]['file']=array();
                                    if($files)
                                    {
                                        foreach($files as $k11=> $file)
                                        {
                                            $data['other_doc'][$key]['file'][$k11]['path']= URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/moredocument/'.$file->file_name.'');
                                            $data['other_doc'][$key]['file'][$k11]['name']=$file->file_name ;
                                        }
                                    } 
                                }
                            }
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Personal Loan Details!'; 
                            $result   = ['detail' => $data];
                            $associate_status=$member->associate_app_status;                  

                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                        }
                        else
                        {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Record not found !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }
                            
                    }
                    else
                    {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter loan id  !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);  
                    }
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['detail' => $data];
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['detail' => $data];
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['detail' => $data];
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }


    public function staffLoanDetail(Request $request)
    {   

        $data = array();
                        $data['loan_details']['lable']='';
                        $data['loan_details']['type']=00; 
                        $data['loan_details']['loan_amount']='';
                        $data['loan_details']['EMI_mode_option']='';
                        $data['loan_details']['loan_purpose']='';
                        $data['loan_details']['associate_code']='';
                        $data['loan_details']['bank_acount']='';


                        $data['loan_details']['ifsc']='';
                        $data['loan_details']['bank_name']='';
                        $data['loan_details']['applicant_id']='';
                        $data['applicant']=array(); 
                        $data['guarantor_applicant']=array();
                        $data['other_doc']=array();



        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->id>0 )
                    {

                        $id=$request->id;
                        $loan_data = $loanDetails= Memberloans::with('loan','LoanApplicants','LoanGuarantor','Loanotherdocs')->where('id',$id)->where('loan_type',2)->first();  
                        if($loan_data)
                        {  
                            $data['loan_details']['lable']='Staff Loan Details';
                            
                             $data['loan_details']['type']=$loanDetails->loan_type; 

                            $data['loan_details']['loan_amount']=$loanDetails->amount;
                            if($loanDetails->emi_option == 1)
                                $emi_mode=$loanDetails->emi_period.' Months';
                            elseif($loanDetails->emi_option == 2)
                                 $emi_mode=$loanDetails->emi_period.' Weeks';
                             elseif($loanDetails->emi_option == 3)
                                $emi_mode=$loanDetails->emi_period.' Days';

                            $data['loan_details']['EMI_mode_option']=$emi_mode;
                            $data['loan_details']['loan_purpose']=$loanDetails->loan_purpose;

                            $data['loan_details']['associate_code']=getAssociateId($loanDetails->associate_member_id);
                            $data['loan_details']['bank_acount']=$loanDetails->bank_account;
                            $data['loan_details']['ifsc']= $loanDetails->ifsc_code;
                            $data['loan_details']['bank_name']=$loanDetails->bank_name;
                            $data['loan_details']['applicant_id']=getApplicantid($loanDetails->applicant_id);

                            foreach($loanDetails['LoanApplicants'] as $key =>$LoanApplicant)
                            {
                                $aDetails = getMemberData($loanDetails->applicant_id);

                                $data['applicant'][$key]['details']['applicant_id']=$aDetails->member_id;
                                $data['applicant'][$key]['details']['name']=$aDetails->first_name.' '.$aDetails->last_name ;
                                $data['applicant'][$key]['details']['father_name']=$aDetails->father_husband;
                                $data['applicant'][$key]['details']['mobile_no']=$aDetails->mobile_no;
                                $data['applicant'][$key]['details']['email_id']=$aDetails->email;
                                $permanent_address='';
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanApplicant->occupation);
                                $data['applicant'][$key]['employment_details']['organization']=$LoanApplicant->organization;
                                $data['applicant'][$key]['employment_details']['designation']=$LoanApplicant->designation;
                                $data['applicant'][$key]['employment_details']['monthly_income']=$LoanApplicant->monthly_income;
                                $data['applicant'][$key]['employment_details']['year_from']=$LoanApplicant->year_from;

                                $data['applicant'][$key]['bank_detail']['bank_name']=$LoanApplicant->bank_name;
                                $data['applicant'][$key]['bank_detail']['bank_ac']=$LoanApplicant->bank_account_number;
                                $data['applicant'][$key]['bank_detail']['ifsc']=$LoanApplicant->ifsc_code;
                                $data['applicant'][$key]['bank_detail']['cheque1']=$LoanApplicant->cheque_number_1;
                                $data['applicant'][$key]['bank_detail']['cheque2']=$LoanApplicant->cheque_number_2;
                                if($LoanApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['applicant'][$key]['documents']['id_no']=$LoanApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanApplicant->id_proof_file_id);
                                $data['applicant'][$key]['documents']['upload_file']=array();

                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['applicant'][$key]['documents']['address_id_proof']=$LoanApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanApplicant->address_proof_file_id);
                                $data['applicant'][$key]['documents']['address_upload_file']=array();

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanApplicant->income_remark)
                                {
                                    $income_remark=$LoanApplicant->income_remark;
                                }

                                $data['applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanApplicant->income_file_id);
                                $data['applicant'][$key]['documents']['income_upload_file']=array();
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }  
                                if($LoanApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['applicant'][$key]['documents']['security']=$security;
                            }

                            

                             // Guarantor Details
                            foreach($loanDetails['LoanGuarantor'] as $key=>$LoanGuarantor)
                            {
                                
                                $gaDetails = getMemberData($LoanGuarantor->member_id);
                                if($gaDetails ) 
                                { $a=$gaDetails->member_id ;
                                }else{ $a='N/A'; }

                                if($gaDetails ) 
                                { $email=$gaDetails->email ;
                                }else{ $email='N/A'; }

                                $data['guarantor_applicant'][$key]['details']['applicant_id']=$a;
                                $data['guarantor_applicant'][$key]['details']['name']=$LoanGuarantor->name ;
                                $data['guarantor_applicant'][$key]['details']['father_name']=$LoanGuarantor->father_name;
                                $data['guarantor_applicant'][$key]['details']['dob']=date("m/d/Y", strtotime(convertDate($LoanGuarantor->dob)));
                                $data['guarantor_applicant'][$key]['details']['email_id']=$email;
                                $permanent_address='';
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['guarantor_applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['guarantor_applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['guarantor_applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanGuarantor->occupation);
                                $data['guarantor_applicant'][$key]['employment_details']['organization']=$LoanGuarantor->organization;
                                $data['guarantor_applicant'][$key]['employment_details']['designation']=$LoanGuarantor->designation;
                                $data['guarantor_applicant'][$key]['employment_details']['monthly_income']=$LoanGuarantor->monthly_income;
                                $data['guarantor_applicant'][$key]['employment_details']['year_from']=$LoanGuarantor->year_from;

                                $data['guarantor_applicant'][$key]['bank_detail']['bank_name']=$LoanGuarantor->bank_name;
                                $data['guarantor_applicant'][$key]['bank_detail']['bank_ac']=$LoanGuarantor->bank_account_number;
                                $data['guarantor_applicant'][$key]['bank_detail']['ifsc']=$LoanGuarantor->ifsc_code;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque1']=$LoanGuarantor->cheque_number_1;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque2']=$LoanGuarantor->cheque_number_2;
                                if($LoanGuarantor->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanGuarantor->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanGuarantor->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanGuarantor->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanGuarantor->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['guarantor_applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['guarantor_applicant'][$key]['documents']['id_no']=$LoanGuarantor->id_proof_number;
                                $applicantFiles = getFileData($LoanGuarantor->id_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['upload_file'] =array();

                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/id_proof/'.$applicantFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                }  
                                if($LoanGuarantor->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanGuarantor->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanGuarantor->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanGuarantor->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanGuarantor->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanGuarantor->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanGuarantor->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanGuarantor->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['guarantor_applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['guarantor_applicant'][$key]['documents']['address_id_proof']=$LoanGuarantor->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanGuarantor->address_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['address_upload_file'] =array(); 

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 

                                $coapplicantUnderDocFiles = getFileData($LoanGuarantor->under_taking_doc);
                                $data['guarantor_applicant'][$key]['documents']['under_taking_doc'] =array(); 

                                if($coapplicantUnderDocFiles)
                                {
                                    foreach($coapplicantUnderDocFiles as $k1=> $coapplicantUnderDocFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['name']=$coapplicantUnderDocFile->file_name ;
                                    }
                                } 


                                if($LoanGuarantor->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanGuarantor->income_type==1)
                                    $income='ITR';
                                elseif($LoanGuarantor->income_type==2)
                                    $income='Others ';  
                               
                                $data['guarantor_applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanGuarantor->income_remark)
                                {
                                    $income_remark=$LoanGuarantor->income_remark;
                                }
                                $data['guarantor_applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanGuarantor->income_file_id);
                                $data['guarantor_applicant'][$key]['documents']['income_upload_file'] =array(); 
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/income_proof/'.$applicantIncomeFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }   
                                if($LoanGuarantor->security==0)
                                    $security='Cheuqe';
                                elseif($LoanGuarantor->security==1)
                                    $security='Passbook';
                                elseif($LoanGuarantor->security==2)
                                    $security='FD Certificate'; 

                                $data['guarantor_applicant'][$key]['documents']['security']=$security;
                            }
                            $data['other_doc']=array();
                            if ( count( $loanDetails['Loanotherdocs'] ) > 0)
                            {
                                foreach($loanDetails['Loanotherdocs'] as $key=>$loanotherdocs)
                                { 

                                    $data['other_doc'][$key]['title']=$loanotherdocs['title'] ;

                                    $files = getFileData($loanotherdocs['file_id']);
                                    $data['other_doc'][$key]['file']=array();
                                    if($files)
                                    {
                                        foreach($files as $k11=> $file)
                                        {
                                            $data['other_doc'][$key]['file'][$k11]['path']= URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/moredocument/'.$file->file_name.'');
                                            $data['other_doc'][$key]['file'][$k11]['name']=$file->file_name ;
                                        }
                                    } 
                                }
                            }
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Staff Loan Details!'; 
                            $result   = ['detail' => $data];
                            $associate_status=$member->associate_app_status;                  

                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                        }
                        else
                        {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Record not found !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }
                            
                    }
                    else
                    {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter loan id  !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);  
                    }
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['detail' => $data];
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['detail' => $data];
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['detail' => $data];
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }



    public function investmentLoanDetail(Request $request)
    {   

        $data = array();
                        $data['loan_details']['lable']='';
                        $data['loan_details']['type']=00; 
                        $data['loan_details']['loan_amount']='';
                        $data['loan_details']['EMI_mode_option']='';
                        $data['loan_details']['loan_purpose']='';
                        $data['loan_details']['associate_code']='';
                        $data['loan_details']['bank_acount']='';


                        $data['loan_details']['ifsc']='';
                        $data['loan_details']['bank_name']='';
                        $data['loan_details']['applicant_id']='';
                        $data['applicant_deposite_detail']=array();
                        $data['applicant']=array();




        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->id>0 )
                    {

                        $id=$request->id;
                        $loan_data = $loanDetails= Memberloans::with('loan','LoanApplicants','loanInvestmentPlans')->where('id',$id)->where('loan_type',4)->first();  
                        if($loan_data)
                        {  
                            $data['loan_details']['lable']='Loan Against Investment Plan Details';
                            
                             $data['loan_details']['type']=$loanDetails->loan_type; 

                            $data['loan_details']['loan_amount']=$loanDetails->amount;
                            if($loanDetails->emi_option == 1)
                                $emi_mode=$loanDetails->emi_period.' Months';
                            elseif($loanDetails->emi_option == 2)
                                 $emi_mode=$loanDetails->emi_period.' Weeks';
                             elseif($loanDetails->emi_option == 3)
                                $emi_mode=$loanDetails->emi_period.' Days';

                            $data['loan_details']['EMI_mode_option']=$emi_mode;
                            $data['loan_details']['loan_purpose']=$loanDetails->loan_purpose;

                            $data['loan_details']['associate_code']=getAssociateId($loanDetails->associate_member_id);
                            $data['loan_details']['bank_acount']=$loanDetails->bank_account;
                            $data['loan_details']['ifsc']= $loanDetails->ifsc_code;
                            $data['loan_details']['bank_name']=$loanDetails->bank_name;
                            $data['loan_details']['applicant_id']=getApplicantid($loanDetails->applicant_id);

                            foreach($loanDetails['loanInvestmentPlans'] as $key =>$loanInvestmentPlan)
                            {
                                $investmentDetails = getMemberInvestment($loanInvestmentPlan->plan_id);
                                 $data['applicant_deposite_detail'][$key]['scheme']=$investmentDetails->name;
                                 $data['applicant_deposite_detail'][$key]['account_id']=$investmentDetails->account_number;
                                 $data['applicant_deposite_detail'][$key]['open_date']=date('d/m/Y', strtotime($investmentDetails->created_at));
                                 $data['applicant_deposite_detail'][$key]['due_date']=getDueDate($investmentDetails->created_at,$investmentDetails->tenure);
                                 $data['applicant_deposite_detail'][$key]['deposit']=$investmentDetails->deposite_amount;
                                 $data['applicant_deposite_detail'][$key]['tenure']=$investmentDetails->tenure*12;
                                 $data['applicant_deposite_detail'][$key]['loan_amount']=$loanInvestmentPlan->amount;
                            }


                            foreach($loanDetails['LoanApplicants'] as $key =>$LoanApplicant)
                            {
                                $aDetails = getMemberData($loanDetails->applicant_id);

                                $data['applicant'][$key]['details']['applicant_id']=$aDetails->member_id;
                                $data['applicant'][$key]['details']['name']=$aDetails->first_name.' '.$aDetails->last_name ;
                                $data['applicant'][$key]['details']['father_name']=$aDetails->father_husband;
                                $data['applicant'][$key]['details']['mobile_no']=$aDetails->mobile_no;
                                $data['applicant'][$key]['details']['email_id']=$aDetails->email;
                                $permanent_address='';
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanApplicant->occupation);
                                $data['applicant'][$key]['employment_details']['organization']=$LoanApplicant->organization;
                                $data['applicant'][$key]['employment_details']['designation']=$LoanApplicant->designation;
                                $data['applicant'][$key]['employment_details']['monthly_income']=$LoanApplicant->monthly_income;
                                $data['applicant'][$key]['employment_details']['year_from']=$LoanApplicant->year_from;

                                $data['applicant'][$key]['bank_detail']['bank_name']=$LoanApplicant->bank_name;
                                $data['applicant'][$key]['bank_detail']['bank_ac']=$LoanApplicant->bank_account_number;
                                $data['applicant'][$key]['bank_detail']['ifsc']=$LoanApplicant->ifsc_code;
                                $data['applicant'][$key]['bank_detail']['cheque1']=$LoanApplicant->cheque_number_1;
                                $data['applicant'][$key]['bank_detail']['cheque2']=$LoanApplicant->cheque_number_2;
                                if($LoanApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['applicant'][$key]['documents']['id_no']=$LoanApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanApplicant->id_proof_file_id);
                                $data['applicant'][$key]['documents']['upload_file']=array();
                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['applicant'][$key]['documents']['address_id_proof']=$LoanApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanApplicant->address_proof_file_id);
                                $data['applicant'][$key]['documents']['address_upload_file']=array();
                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanApplicant->income_remark)
                                {
                                    $income_remark=$LoanApplicant->income_remark;
                                }

                                $data['applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanApplicant->income_file_id);
                                $data['applicant'][$key]['documents']['income_upload_file']=array();
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['applicant'][$key]['documents']['security']=$security;
                            }

                            

                             
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Loan Against Investment Plan Details!'; 
                            $result   = ['detail' => $data];
                            $associate_status=$member->associate_app_status;                  

                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                        }
                        else
                        {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Record not found !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }
                            
                    }
                    else
                    {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter loan id  !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);  
                    }
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['detail' => $data];
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['detail' => $data];
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['detail' => $data];
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }


    public function groupLoanDetail(Request $request)
    {   

        $data = array();
                        $data['loan_details']['lable']='';
                        $data['loan_details']['type']=00; 
                        $data['loan_details']['group_activity']='';
                        $data['loan_details']['group_leader']='';
                        $data['loan_details']['no_of_member']='';
                        $data['loan_details']['loan_amount']='';
                        $data['loan_details']['EMI_mode_option']=''; 
                        $data['group_member_detail']=array();
                        $data['applicant']=array();
                        $data['co_applicant']=array();

                        $data['guarantor_applicant']=array();
                        $data['other_doc']=array();



        $associate_no = $request->associate_no;     
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($request->id>0 )
                    {

                        $id=$request->id;
                        $loan_data = $loanDetails= Memberloans::with('loan','LoanApplicants','LoanGuarantor','Loanotherdocs','GroupLoanMembers')->where('id',$id)->where('loan_type',3)->first();  
                        if($loan_data)
                        {  
                            $data['loan_details']['lable']='Group Loan Details';
                            
                             $data['loan_details']['type']=$loanDetails->loan_type; 

                             $data['loan_details']['group_activity']=getGroupLoanDetail($loanDetails->id)->group_activity ;
                             $data['loan_details']['group_leader']=getMemberData(getGroupLoanDetail($loanDetails->id)->groupleader_member_id)->member_id ;
                             $data['loan_details']['no_of_member']=count($loanDetails['GroupLoanMembers']);
                             



                            $data['loan_details']['loan_amount']=$loanDetails->amount;
                            if($loanDetails->emi_option == 1)
                                $emi_mode=$loanDetails->emi_period.' Months';
                            elseif($loanDetails->emi_option == 2)
                                 $emi_mode=$loanDetails->emi_period.' Weeks';
                             elseif($loanDetails->emi_option == 3)
                                $emi_mode=$loanDetails->emi_period.' Days';

                            $data['loan_details']['EMI_mode_option']=$emi_mode;
                            

                            $data['loan_details']['associate_code']=getAssociateId($loanDetails->associate_member_id); 
                            foreach($loanDetails['GroupLoanMembers'] as $key =>$groupLoanMember)
                            {
                                $mDetails = getMemberData($groupLoanMember->member_id);
                                $data['group_member_detail'][$key]['member_id']=$mDetails->member_id;
                                $data['group_member_detail'][$key]['member_name']= $mDetails->first_name.' '.$mDetails->last_name;
                                $data['group_member_detail'][$key]['father_name']=$mDetails->father_husband;
                                $data['group_member_detail'][$key]['amount']=$groupLoanMember->amount;
                                $gbank_name='';
                                    $gssb_account='';
                                    $gifsc='';
                                if(count($mDetails['memberBankDetails']) > 0)
                                {
                                    $gbank_name=$mDetails['memberBankDetails'][0]->bank_name;
                                    $gssb_account=$mDetails['memberBankDetails'][0]->account_no ;
                                    $gifsc=$mDetails['memberBankDetails'][0]->ifsc_code;
                                }
                                $data['group_member_detail'][$key]['bank_name']=$gbank_name;
                                $data['group_member_detail'][$key]['ssb_account']=$gssb_account;
                                $data['group_member_detail'][$key]['ifsc']=$gifsc;
                            }


                            foreach($loanDetails['LoanApplicants'] as $key =>$LoanApplicant)
                            {
                                $aDetails = getMemberData($loanDetails->applicant_id);

                                $data['applicant'][$key]['details']['applicant_id']=$aDetails->member_id;
                                $data['applicant'][$key]['details']['name']=$aDetails->first_name.' '.$aDetails->last_name ;
                                $data['applicant'][$key]['details']['father_name']=$aDetails->father_husband;
                                $data['applicant'][$key]['details']['mobile_no']=$aDetails->mobile_no;
                                $data['applicant'][$key]['details']['email_id']=$aDetails->email;
                                $data['applicant'][$key]['details']['member_id']=getApplicantid($loanDetails->group_member_id);
                                $permanent_address='';
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanApplicant->occupation);
                                $data['applicant'][$key]['employment_details']['organization']=$LoanApplicant->organization;
                                $data['applicant'][$key]['employment_details']['designation']=$LoanApplicant->designation;
                                $data['applicant'][$key]['employment_details']['monthly_income']=$LoanApplicant->monthly_income;
                                $data['applicant'][$key]['employment_details']['year_from']=$LoanApplicant->year_from;

                                $data['applicant'][$key]['bank_detail']['bank_name']=$LoanApplicant->bank_name;
                                $data['applicant'][$key]['bank_detail']['bank_ac']=$LoanApplicant->bank_account_number;
                                $data['applicant'][$key]['bank_detail']['ifsc']=$LoanApplicant->ifsc_code;
                                $data['applicant'][$key]['bank_detail']['cheque1']=$LoanApplicant->cheque_number_1;
                                $data['applicant'][$key]['bank_detail']['cheque2']=$LoanApplicant->cheque_number_2;
                                if($LoanApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['applicant'][$key]['documents']['id_no']=$LoanApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanApplicant->id_proof_file_id);

                                $data['applicant'][$key]['documents']['upload_file']=array();
                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['applicant'][$key]['documents']['address_id_proof']=$LoanApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanApplicant->address_proof_file_id);
                                 $data['applicant'][$key]['documents']['address_upload_file']=array();

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanApplicant->income_remark)
                                {
                                    $income_remark=$LoanApplicant->income_remark;
                                }

                                $data['applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanApplicant->income_file_id);
                                $data['applicant'][$key]['documents']['income_upload_file']=array();
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                } 
                                if($LoanApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['applicant'][$key]['documents']['security']=$security;
                            }
                            // Co-applicant Details

                            foreach($loanDetails['LoanCoApplicants'] as $key =>$LoanCoApplicant)
                            {
                                
                                $caDetails = getMemberData($LoanCoApplicant->member_id);

                                $data['co_applicant'][$key]['details']['applicant_id']=$caDetails->member_id;
                                $data['co_applicant'][$key]['details']['name']=$caDetails->first_name.' '.$caDetails->last_name ;
                                $data['co_applicant'][$key]['details']['father_name']=$caDetails->father_husband;
                                $data['co_applicant'][$key]['details']['mobile_no']=$caDetails->mobile_no;
                                $data['co_applicant'][$key]['details']['email_id']=$caDetails->email;
                                $permanent_address='';
                                if($LoanCoApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanCoApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanCoApplicant->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanCoApplicant->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanCoApplicant->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanCoApplicant->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['co_applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['co_applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['co_applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanCoApplicant->occupation);
                                $data['co_applicant'][$key]['employment_details']['organization']=$LoanCoApplicant->organization;
                                $data['co_applicant'][$key]['employment_details']['designation']=$LoanCoApplicant->designation;
                                $data['co_applicant'][$key]['employment_details']['monthly_income']=$LoanCoApplicant->monthly_income;
                                $data['co_applicant'][$key]['employment_details']['year_from']=$LoanCoApplicant->year_from;

                                $data['co_applicant'][$key]['bank_detail']['bank_name']=$LoanCoApplicant->bank_name;
                                $data['co_applicant'][$key]['bank_detail']['bank_ac']=$LoanCoApplicant->bank_account_number;
                                $data['co_applicant'][$key]['bank_detail']['ifsc']=$LoanCoApplicant->ifsc_code;
                                $data['co_applicant'][$key]['bank_detail']['cheque1']=$LoanCoApplicant->cheque_number_1;
                                $data['co_applicant'][$key]['bank_detail']['cheque2']=$LoanCoApplicant->cheque_number_2;
                                if($LoanCoApplicant->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanCoApplicant->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanCoApplicant->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanCoApplicant->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanCoApplicant->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['co_applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['co_applicant'][$key]['documents']['id_no']=$LoanCoApplicant->id_proof_number;
                                $applicantFiles = getFileData($LoanCoApplicant->id_proof_file_id);
                                $data['co_applicant'][$key]['documents']['upload_file'] =array();

                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/id_proof/'.$applicantFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanCoApplicant->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanCoApplicant->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanCoApplicant->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanCoApplicant->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanCoApplicant->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanCoApplicant->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanCoApplicant->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanCoApplicant->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['co_applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['co_applicant'][$key]['documents']['address_id_proof']=$LoanCoApplicant->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanCoApplicant->address_proof_file_id);
                                $data['co_applicant'][$key]['documents']['address_upload_file'] =array(); 

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                } 

                                $coapplicantUnderDocFiles = getFileData($LoanCoApplicant->under_taking_doc);
                                $data['co_applicant'][$key]['documents']['under_taking'] =array();

                                if($coapplicantUnderDocFiles)
                                {
                                    foreach($coapplicantUnderDocFiles as $k1=> $coapplicantUnderDocFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['under_taking _doc'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'');
                                        $data['co_applicant'][$key]['documents']['under_taking'][$k1]['name']=$coapplicantUnderDocFile->file_name ;
                                    }
                                } 


                                if($LoanCoApplicant->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanCoApplicant->income_type==1)
                                    $income='ITR';
                                elseif($LoanCoApplicant->income_type==2)
                                    $income='Others ';  
                               
                                $data['co_applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanCoApplicant->income_remark)
                                {
                                    $income_remark=$LoanCoApplicant->income_remark;
                                }
                                $data['co_applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanCoApplicant->income_file_id);
                                $data['co_applicant'][$key]['documents']['income_upload_file'] =array(); 
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['co_applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/coapplicant/income_proof/'.$applicantIncomeFile->file_name.'') ;
                                        $data['co_applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                }  
                                if($LoanCoApplicant->security==0)
                                    $security='Cheuqe';
                                elseif($LoanCoApplicant->security==1)
                                    $security='Passbook';
                                elseif($LoanCoApplicant->security==2)
                                    $security='FD Certificate'; 

                                $data['co_applicant'][$key]['documents']['security']=$security;
                            }

                            
                             // Guarantor Details
                            foreach($loanDetails['LoanGuarantor'] as $key=>$LoanGuarantor)
                            {
                                
                                $gaDetails = getMemberData($LoanGuarantor->member_id);
                                if($gaDetails ) 
                                { $a=$gaDetails->member_id ;
                                }else{ $a='N/A'; }

                                if($gaDetails ) 
                                { $email=$gaDetails->email ;
                                }else{ $email='N/A'; }

                                $data['guarantor_applicant'][$key]['details']['applicant_id']=$a;
                                $data['guarantor_applicant'][$key]['details']['name']=$LoanGuarantor->name ;
                                $data['guarantor_applicant'][$key]['details']['father_name']=$LoanGuarantor->father_name;
                                $data['guarantor_applicant'][$key]['details']['dob']=date("m/d/Y", strtotime(convertDate($LoanGuarantor->dob)));
                                $data['guarantor_applicant'][$key]['details']['email_id']=$email;
                                $permanent_address='';
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental'; 
                                if($LoanGuarantor->address_permanent==0)
                                    $permanent_address='Self';
                                elseif($LoanGuarantor->address_permanent==1)
                                    $permanent_address='Perental';
                                elseif($LoanGuarantor->address_permanent==2)
                                    $permanent_address='Rental';

                                $data['guarantor_applicant'][$key]['details']['permanent_address']=$permanent_address;
                                $data['guarantor_applicant'][$key]['details']['temporary_address']=$emi_mode;
                                $data['guarantor_applicant'][$key]['employment_details']['occupation']=getOccupationName($LoanGuarantor->occupation);
                                $data['guarantor_applicant'][$key]['employment_details']['organization']=$LoanGuarantor->organization;
                                $data['guarantor_applicant'][$key]['employment_details']['designation']=$LoanGuarantor->designation;
                                $data['guarantor_applicant'][$key]['employment_details']['monthly_income']=$LoanGuarantor->monthly_income;
                                $data['guarantor_applicant'][$key]['employment_details']['year_from']=$LoanGuarantor->year_from;

                                $data['guarantor_applicant'][$key]['bank_detail']['bank_name']=$LoanGuarantor->bank_name;
                                $data['guarantor_applicant'][$key]['bank_detail']['bank_ac']=$LoanGuarantor->bank_account_number;
                                $data['guarantor_applicant'][$key]['bank_detail']['ifsc']=$LoanGuarantor->ifsc_code;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque1']=$LoanGuarantor->cheque_number_1;
                                $data['guarantor_applicant'][$key]['bank_detail']['cheque2']=$LoanGuarantor->cheque_number_2;
                                if($LoanGuarantor->id_proof_type==0)
                                    $id_proof='Pen Card';
                                elseif($LoanGuarantor->id_proof_type==1)
                                    $id_proof='Aadhar Card';
                                elseif($LoanGuarantor->id_proof_type==2)
                                    $id_proof='DL';
                                elseif($LoanGuarantor->id_proof_type==3)
                                    $id_proof='Voter Id';
                               elseif($LoanGuarantor->id_proof_type==4)
                                    $id_proof='Passport';
                                else
                                    $id_proof='Identity Card';   
                               
                                $data['guarantor_applicant'][$key]['documents']['id_proof']=$id_proof;
                                $data['guarantor_applicant'][$key]['documents']['id_no']=$LoanGuarantor->id_proof_number;
                                $applicantFiles = getFileData($LoanGuarantor->id_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['upload_file'] =array(); 
                                if($applicantFiles)
                                {
                                    foreach($applicantFiles as $k=> $applicantFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/id_proof/'.$applicantFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['upload_file'][$k]['name']=$applicantFile->file_name ;
                                    }
                                } 
                                if($LoanGuarantor->address_proof_type==0)
                                    $address_proof='Aadhar Card'; 
                                elseif($LoanGuarantor->address_proof_type==1)
                                    $address_proof='DL'; 
                                elseif($LoanGuarantor->address_proof_type==2)
                                    $address_proof='Voter Id'; 
                                elseif($LoanGuarantor->address_proof_type==3)
                                    $address_proof='Passport'; 
                                elseif($LoanGuarantor->address_proof_type==4)
                                    $address_proof='Identity Card ';   
                                elseif($LoanGuarantor->address_proof_type==5)
                                    $address_proof='Bank Passbook';   
                                elseif($LoanGuarantor->address_proof_type==6)
                                    $address_proof='Electricity Bill'; 
                                elseif($LoanGuarantor->address_proof_type==7)
                                    $address_proof='Telephone Bill'; 
                                
                                $data['guarantor_applicant'][$key]['documents']['address_proof']=$address_proof;
                                $data['guarantor_applicant'][$key]['documents']['address_id_proof']=$LoanGuarantor->address_proof_id_number;

                                $applicantAddressFiles = getFileData($LoanGuarantor->address_proof_file_id);
                                $data['guarantor_applicant'][$key]['documents']['address_upload_file'] =array(); 

                                if($applicantAddressFiles)
                                {
                                    foreach($applicantAddressFiles as $k1=> $applicantAddressFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/address_proof/'.$applicantAddressFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['address_upload_file'][$k1]['name']=$applicantAddressFile->file_name ;
                                    }
                                }  

                                $coapplicantUnderDocFiles = getFileData($LoanGuarantor->under_taking_doc);
                                $data['guarantor_applicant'][$key]['documents']['under_taking_doc'] =array(); 
                                if($coapplicantUnderDocFiles)
                                {
                                    foreach($coapplicantUnderDocFiles as $k1=> $coapplicantUnderDocFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['under_taking_doc'][$k1]['name']=$coapplicantUnderDocFile->file_name ;
                                    }
                                } 


                                if($LoanGuarantor->income_type==0)
                                    $income='Salary Slip ';
                                elseif($LoanGuarantor->income_type==1)
                                    $income='ITR';
                                elseif($LoanGuarantor->income_type==2)
                                    $income='Others ';  
                               
                                $data['guarantor_applicant'][$key]['documents']['income']=$income;
                                $income_remark='';
                                if($LoanGuarantor->income_remark)
                                {
                                    $income_remark=$LoanGuarantor->income_remark;
                                }
                                $data['guarantor_applicant'][$key]['documents']['income_remark']=$income_remark;
                                $applicantIncomeFiles = getFileData($LoanGuarantor->income_file_id);
                                $data['guarantor_applicant'][$key]['documents']['income_upload_file'] =array(); 
                                if($applicantIncomeFiles)
                                {
                                    foreach($applicantIncomeFiles as $k11=> $applicantIncomeFile)
                                    {
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['path']=URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/income_proof/'.$applicantIncomeFile->file_name.'');
                                        $data['guarantor_applicant'][$key]['documents']['income_upload_file'][$k11]['name']=$applicantIncomeFile->file_name ;
                                    }
                                } 
                                if($LoanGuarantor->security==0)
                                    $security='Cheuqe';
                                elseif($LoanGuarantor->security==1)
                                    $security='Passbook';
                                elseif($LoanGuarantor->security==2)
                                    $security='FD Certificate'; 

                                $data['guarantor_applicant'][$key]['documents']['security']=$security;
                            }
                            $data['other_doc']=array();
                            if ( count( $loanDetails['Loanotherdocs'] ) > 0)
                            {
                                foreach($loanDetails['Loanotherdocs'] as $key=>$loanotherdocs)
                                { 

                                    $data['other_doc'][$key]['title']=$loanotherdocs['title'] ;
                                    $data['other_doc'][$key]['file']=array();
                                    $files = getFileData($loanotherdocs['file_id']);
                                    if($files)
                                    {
                                        foreach($files as $k11=> $file)
                                        {
                                            $data['other_doc'][$key]['file'][$k11]['path']= URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/moredocument/'.$file->file_name.'');
                                            $data['other_doc'][$key]['file'][$k11]['name']=$file->file_name ;
                                        }
                                    } 
                                }
                            }
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Group Loan Details!'; 
                            $result   = ['detail' => $data];
                            $associate_status=$member->associate_app_status;                  

                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                        }
                        else
                        {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Record not found !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }
                            
                    }
                    else
                    {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter loan id  !';
                            $result = ['detail' => $data];
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);  
                    }
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['detail' => $data];
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['detail' => $data];
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['detail' => $data];
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }
    
}

