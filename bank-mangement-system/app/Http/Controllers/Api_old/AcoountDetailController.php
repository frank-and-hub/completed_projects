<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use Carbon\Carbon;

class AcoountDetailController extends Controller
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


    public function accountTranscation(Request $request)
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

                        $accountData=\App\Models\Memberinvestments::with('plan')->where('account_number',$request->account_no)->first();
                        if($accountData)
                        {
                            if($accountData['plan']->plan_code==703)
                            {
                                $ssb = SavingAccount::where('member_investments_id',$accountData->id)->first();
                                $transcation =SavingAccountTranscation::where('saving_account_id',$ssb->id)->orderBy('created_at','DESC')->orderBy('id','DESC');   
                                $transcation1=$transcation->get();
                                $count=count($transcation1);
                                if($request->page==1)
                                {
                                    $start=0;
                                }
                                else
                                {
                                    //$start=$request->page*$request->length;
                                    $start=($request->page-1)*$request->length;
                                }
                                
                                $transcation=$transcation->offset($start)->limit($request->length)->get();

                                foreach ($transcation as $key => $value) 
                                {
                                    $data[$key]['is_ssb'] = 1;
                                    $data[$key]['tranid'] = $value->id;
                                    $data[$key]['date'] =  date("d/m/Y", strtotime($value->created_at));
                                    $data[$key]['description'] = $value->description;
                                    $data[$key]['reference_no'] = $value->cheque_dd_no;
                                    if($value->withdrawal>0)
                                    {
                                        $withdrawal = $value->withdrawal;
                                    }
                                    else
                                    {
                                        $withdrawal = '';
                                    }                    
                                    $data[$key]['withdrawal'] = $withdrawal;
                                    if($value->deposit>0)
                                    {
                                        $deposit = $value->deposit;
                                    }
                                    else
                                    {
                                        $deposit = '';
                                    } 
                                    $data[$key]['deposit'] = $deposit;
                                    $data[$key]['opening_balance'] = $value->opening_balance;
                                }
                            }
                            else
                            { 
                                $transcation = Daybook::where('investment_id',$accountData->id)->where(function($q) { $q->whereIN('transaction_type', [2,4,15,16]); } )->orderBy('created_at','DESC')->orderBy('id','DESC');   
                                $transcation1=$transcation->get();
                                $count=count($transcation1);
                                if($request->page==1)
                                {
                                    $start=0;
                                }
                                else
                                {
                                    $start=($request->page-1)*$request->length;
                                }
                                
                                $transcation=$transcation->offset($start)->limit($request->length)->get();

                                foreach ($transcation as $key => $value) 
                                {
                                    $data[$key]['is_ssb'] = 0;
                                    $data[$key]['tranid'] = $value->id;
                                    $data[$key]['date'] =  date("d/m/Y", strtotime($value->created_at));
                                    $data[$key]['description'] = $value->description;
                                    $reference_no='';
                                      if($value->payment_mode==1)
                                      {
                                        $reference_no = $value->cheque_dd_no;
                                      }
                                      if($value->payment_mode==4 || $value->payment_mode==5 )
                                      {
                                        $reference_no = $value->reference_no;
                                      }
                                      if($value->payment_mode==3)
                                      {
                                        $reference_no = $value->online_payment_id;
                                      }
                                    $data[$key]['reference_no'] = $reference_no;
                                    if($value->withdrawal>0)
                                    {
                                        $withdrawal = $value->withdrawal;
                                    }
                                    else
                                    {
                                        $withdrawal = '';
                                    }                    
                                    $data[$key]['withdrawal'] = $withdrawal;
                                    if($value->deposit>0)
                                    {
                                        $deposit = $value->deposit;
                                    }
                                    else
                                    {
                                        $deposit = '';
                                    } 
                                    $data[$key]['deposit'] = $deposit;
                                    $data[$key]['opening_balance'] = $value->opening_balance;
                                }
                            }

                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Acoount Transcation listing!';
                            $page  = $request->page;
                            $length  = $request->length;
                            $result   = ['transcation' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length,'account_no'=>$request->account_no,'record_count'=>count($data)];
                            $associate_status=$member->associate_app_status;
                            
                            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                        }
                        else
                        {
                           $status = "Error";
                            $code = 201;
                            $messages = 'Account information not found';
                            $result = '';
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }

                    
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


    public function transcationDetail(Request $request)
    {   
        $associate_no = $request->associate_no;  
        
        try { 

        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();

            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){ 
                    $data=array();

                        if($request->is_ssb!=''  && $request->tranid!='')
                        {
                            if($request->is_ssb==1)
                            {
                               $tDetails= SavingAccountTranscation::where('id',$request->tranid)->first(); 
                               $mId = SavingAccount::select('member_investments_id','member_id','account_no')->where('id',$tDetails->saving_account_id)->first(); 
                               $aId = Memberinvestments::select('associate_id')->where('id',$mId->member_investments_id)->first(); 
                               $memberID = $mId->member_id;        
                                if($tDetails->associate_id>0)
                                {
                                    $associate_id = $tDetails->associate_id;
                                }
                                else
                                {
                                    $associate_id = $aId->associate_id;
                                }
                                $memberDetail = Member::find($memberID);
                                $associateDetail = Member::find($associate_id);
                                $data['tranid'] =$tDetails->id;
                                $data['date'] =date("d/m/Y", strtotime( str_replace('-','/',$tDetails->created_at)));
                                $data['name'] =$memberDetail->first_name.' '.$memberDetail->last_name;
                                $data['account_no'] =$mId->account_no;
                                $data['description'] =$tDetails->description;
                                if($tDetails->payment_mode == 0)
                                    $payment_mode='Cash';
                                elseif($tDetails->payment_mode == 1)
                                    $payment_mode='Cheque';
                                elseif($tDetails->payment_mode == 2)
                                    $payment_mode='DD';
                                elseif($tDetails->payment_mode == 3)
                                    if($tDetails->deposit>0)
                                        $payment_mode='Transfer By Other Account'; 
                                    else
                                        $payment_mode='Transfer To Other Account'; 
                                elseif($tDetails->payment_mode == 4)
                                    if($tDetails->deposit>0)
                                        $payment_mode='From SSB';
                                    else
                                        $payment_mode='SSB Transfer';
                                    
                                elseif($tDetails->payment_mode == 5)
                                   $payment_mode='Online'; 
                                $data['payment_mode'] =$payment_mode;
                                $data['amount_type'] =0;
                                if($tDetails->deposit>0)
                                {
                                    $data['amount_type'] =1;
                                }
                                if($tDetails->withdrawal>0)
                                {
                                    $data['amount_type'] =2;
                                }
                                
                                $data['deposit'] =$tDetails->deposit;
                                $data['withdrawal'] =$tDetails->withdrawal;
                                $data['opening_balance'] =$tDetails->opening_balance;

                                $data['associate_name'] =$associateDetail->first_name.' '.$associateDetail->last_name;
                                $data['associate_code'] =$associateDetail->associate_no;
                            }
                            else{
                                 

                                $tDetails= Daybook::where('id',$request->tranid)->first();
                               
                               $memberID = $tDetails->member_id; 
                               $associate_id = $tDetails->associate_id;
                                
                                $memberDetail = Member::find($memberID);
                                $associateDetail = Member::find($associate_id);
                                $data['tranid'] =$tDetails->id;
                                $data['date'] =date("d/m/Y", strtotime( str_replace('-','/',$tDetails->created_at)));
                                $data['name'] =$memberDetail->first_name.' '.$memberDetail->last_name;
                                $data['account_no'] =$tDetails->account_no;
                                $data['description'] =$tDetails->description;
                                if($tDetails->payment_mode == 0)
                                    $payment_mode='Cash';
                                elseif($tDetails->payment_mode == 1)
                                    $payment_mode='Cheque';
                                elseif($tDetails->payment_mode == 2)
                                    $payment_mode='DD';
                                elseif($tDetails->payment_mode == 3)
                                        $payment_mode='Online';
                                elseif($tDetails->payment_mode == 4) 
                                        $payment_mode='From SSB';  
                                elseif($tDetails->payment_mode == 5)
                                   $payment_mode='From Loan Account '; 
                                $data['payment_mode'] =$payment_mode;
                                $data['amount_type'] =0;
                                if($tDetails->deposit>0)
                                {
                                    $data['amount_type'] =1;
                                }
                                if($tDetails->withdrawal>0)
                                {
                                    $data['amount_type'] =2;
                                }
                                $data['deposit'] =$tDetails->deposit;
                                $data['withdrawal'] =$tDetails->withdrawal;
                                $data['opening_balance'] =$tDetails->opening_balance;

                                $data['associate_name'] =$associateDetail->first_name.' '.$associateDetail->last_name;
                                $data['associate_code'] =$associateDetail->associate_no;

                            }

                             

                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Acoount Transcation listing!'; 
                            $result   = ['transaction' => $data];
                            $associate_status=$member->associate_app_status;
                            
                            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                        }
                        else
                        {
                           $status = "Error";
                            $code = 201;
                            $messages = 'Account transaction id not valid ';
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


public function loan_ledger(Request $request)
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

                        $accountData=\App\Models\Memberloans::select('id','loan_type','account_number')->where('account_number',$request->account_no)->first();
                        if($accountData)
                        {
                            
                                $transcation =\App\Models\LoanDayBooks::where('loan_id',$accountData->id);   
                                $transcation1=$transcation->get();
                                $count=count($transcation1);
                                if($request->page==1)
                                {
                                    $start=0;
                                }
                                else
                                {
                                    $start=($request->page-1)*$request->length;
                                }
                                
                                $transcation=$transcation->offset($start)->limit($request->length)->get();

                                foreach ($transcation as $key => $value) 
                                {
                                    
                                    $data[$key]['tranid'] = $value->id;
                                    $data[$key]['date'] =  date("d/m/Y", strtotime($value->payment_date));
                                    $paymentMode = 'N/A';

                                    if($value->payment_mode == 0){

                                        $paymentMode = 'Cash';

                                    }elseif($value->payment_mode == 1){

                                        $paymentMode = 'Cheque';

                                    }elseif($value->payment_mode == 2){

                                        $paymentMode = 'DD';

                                    }elseif($value->payment_mode == 3){

                                        $paymentMode = 'Online Transaction';

                                    }elseif($value->payment_mode == 4){

                                        $paymentMode = 'By Saving Account ';

                                    }
                                    $data[$key]['paymentMode'] = $paymentMode;
                                    $data[$key]['description'] = $value->description;
                                    if($value->loan_sub_type == 1){
                                        $penalty =  $value->deposit;
                                    }else{
                                        $penalty =   'N/A';
                                    }
                                    $data[$key]['penalty'] = $penalty;
                                    $data[$key]['reference_no'] = $value->cheque_dd_no;
                                    if($value->withdrawal>0)
                                    {
                                        $withdrawal = $value->withdrawal;
                                    }
                                    else
                                    {
                                        $withdrawal = '';
                                    }      
                                    if($value->loan_sub_type == 0){
                                        $deposit =  $value->deposit;
                                    }else{
                                        $deposit =   'N/A';
                                    }
                                    $data[$key]['deposit'] = $deposit;
                                    if($value->loan_sub_type == 0){
                                        $roi_amount =  $value->roi_amount;
                                    }else{
                                        $roi_amount =   'N/A';
                                    }
                                    $data[$key]['roi_amount'] = $roi_amount;

                                    if($value->loan_sub_type == 0){
                                        $principal_amount =  $value->principal_amount;
                                    }else{
                                        $principal_amount =   'N/A';
                                    }
                                    $data[$key]['principal_amount'] = $principal_amount;

                                    $data[$key]['opening_balance'] = $value->opening_balance;
                                }
                           

                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Loan account Transcation listing!';
                            $page  = $request->page;
                            $length  = $request->length;
                            $result   = ['transcation' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length,'account_no'=>$request->account_no,'record_count'=>count($data)];
                            $associate_status=$member->associate_app_status;
                            
                            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                        }
                        else
                        {
                           $status = "Error";
                            $code = 201;
                            $messages = 'Account information not found';
                            $result = '';
                            $associate_status=$member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                        }

                    
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
    public function grouploan_ledger(Request $request)
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

                            //$accountData=\App\Models\Grouploans::select('id','account_number')->where('account_number',$request->account_no)->first();

                            $accountData=\App\Models\Memberloans::select('id','account_number')->where('account_number',$request->account_no)->first();
                            if($accountData)
                            {
                                
                                    $transcation =\App\Models\LoanDayBooks::where('loan_id',$accountData->id);  

                                     //$transcation =\App\Models\LoanDayBooks::where('group_loan_id',$accountData->id);  
                                    $transcation1=$transcation->get();
                                    $count=count($transcation1);
                                    if($request->page==1)
                                    {
                                        $start=0;
                                    }
                                    else
                                    {
                                        $start=($request->page-1)*$request->length;
                                    }
                                    
                                    $transcation=$transcation->offset($start)->limit($request->length)->get();

                                    foreach ($transcation as $key => $value) 
                                    {
                                        
                                        $data[$key]['tranid'] = $value->id;
                                        $data[$key]['date'] =  date("d/m/Y", strtotime($value->payment_date));

                                        if($value->payment_mode == 0){

                                            $paymentMode = 'Cash';

                                        }elseif($value->payment_mode == 1){

                                            $paymentMode = 'Cheque';

                                        }elseif($value->payment_mode == 2){

                                            $paymentMode = 'DD';

                                        }elseif($value->payment_mode == 3){

                                            $paymentMode = 'Online Transaction';

                                        }elseif($value->payment_mode == 4){

                                            $paymentMode = 'By Saving Account ';

                                        }
                                        $data[$key]['paymentMode'] = $value->paymentMode;
                                        $data[$key]['description'] = $value->description;
                                        if($value->loan_sub_type == 1){
                                            $penalty =  $value->deposit;
                                        }else{
                                            $penalty =   'N/A';
                                        }
                                        $data[$key]['penalty'] = $penalty;
                                        $data[$key]['reference_no'] = $value->cheque_dd_no;
                                        if($value->withdrawal>0)
                                        {
                                            $withdrawal = $value->withdrawal;
                                        }
                                        else
                                        {
                                            $withdrawal = '';
                                        }      
                                        if($value->loan_sub_type == 0){
                                            $deposit =  $value->deposit;
                                        }else{
                                            $deposit =   'N/A';
                                        }
                                        $data[$key]['deposit'] = $deposit;
                                        if($value->loan_sub_type == 0){
                                            $roi_amount =  $value->roi_amount;
                                        }else{
                                            $roi_amount =   'N/A';
                                        }
                                        $data[$key]['roi_amount'] = $roi_amount;

                                        if($value->loan_sub_type == 0){
                                            $principal_amount =  $value->principal_amount;
                                        }else{
                                            $principal_amount =   'N/A';
                                        }
                                        $data[$key]['principal_amount'] = $principal_amount;

                                        $data[$key]['opening_balance'] = $value->opening_balance;
                                    }
                               

                                $status   = "Success";
                                $code     = 200;
                                $messages = 'Group Loan account Transcation listing!';
                                $page  = $request->page;
                                $length  = $request->length;
                                $result   = ['transcation' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length,'account_no'=>$request->account_no,'record_count'=>count($data)];
                                $associate_status=$member->associate_app_status;
                                
                                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                            }
                            else
                            {
                               $status = "Error";
                                $code = 201;
                                $messages = 'Account information not found';
                                $result = '';
                                $associate_status=$member->associate_app_status;
                                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                            }

                        
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

   

   
}
