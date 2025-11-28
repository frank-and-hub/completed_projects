<?php

namespace App\Http\Controllers\Api;
//die('stop app');
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

class MemberinvestmentController extends Controller
{   

    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function memberInvestments(Request $request)
    {   
        $mid = $request->member_id;
        $aNumber = $request->account_number;
        $member = Member::where('member_id',$mid)->where('is_block',0)->count();
        try {  
        	if ( $member > 0) {
                $token = md5($request->member_id);
                if($token == $request->token){ 
                    $data = array();
                    $mInvestments = Memberinvestments::with('plan','member','associateMember');
                    $mInvestments=$mInvestments->whereHas('member', function ($query) use ($mid) {
                          $query->where('members.member_id',''.$mid.'');
                        });
                    $mInvestments = $mInvestments->where('account_number',$aNumber);
                    $mInvestments = $mInvestments->orderby('id','DESC')->get();

                    foreach ($mInvestments as $key => $value) {
                        $data[$key]['invetment_id'] = $value->id;
                        $data[$key]['account_number'] = $value->account_number;
                        $data[$key]['plan'] = $value['plan']->name;
                        $data[$key]['form_number'] = $value->form_number;
                        $data[$key]['created_at'] =  date("d/m/Y", strtotime( str_replace('-','/',$value->created_at )));
                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Member investment listing!';
                    $result   = ['investments' => $data];
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

    /**
     * Fetch investments transactions.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function investmentTransactions(Request $request)
    {   
        $mId = $request->member_id;
        $iId = $request->investment_id;
        $member = Member::where('member_id',$mId)->where('is_block',0)->count();

        $eliOpeningAmount = 0.00;
        $accountsNumber = array('R-066523000256','R-084511001514','R-066523000257','R-066523000620','R-084523000800','R-066523000510','R-066523000258','R-084504006762','R-066504006287','R-066523000509','R-066523000588','R-084504007930');

        try {
            if ( $member > 0) {
                $token = md5($mId);
                if($token == $request->token){        
                    $investPlan =  Memberinvestments::select('plan_id','account_number','deposite_amount')->where('id',$iId)->first();
                    if($investPlan->plan_id == 1){
                        $data = SavingAccountTranscation::where('account_no',$investPlan->account_number)->orderBy('created_at','DESC')->get(); 


                    
                        foreach ($data as $key => $value) {
                            if($value->amount == ''){
                                $data[$key]['amount'] = '0.00';
                            }

                            if($value->deposit == ''){
                                $data[$key]['deposit'] = '0.00';
                            }

                            if($value->withdrawal == ''){
                                $data[$key]['withdrawal'] = '0.00';
                            }
                            //$data[$key]['opening_balance'] = (string)number_format($value->opening_balance, 2, '.', '');
                        }

                    }else{
                        $eliOpeningAmount = Daybook::where('investment_id',$iId)->whereIn('account_no',$accountsNumber)->where('is_eli', 1)->where(function ($q) { $q->where('transaction_type', 2)->orWhere('transaction_type', 4); } )->pluck('amount')->first();

                        $eliOpeningAmount = $eliOpeningAmount != null ? round($eliOpeningAmount,2) : 0.00;

                        $data = Daybook::where('investment_id',$iId)->where(function($q) { $q->where('transaction_type', 2)->orWhere('transaction_type', 4); } )->orderBy('opening_balance','DESC')->get();

                        foreach ($data as $key => $value) {
                            if ( $value->is_eli == 1 && in_array($value->account_no,$accountsNumber) ){
                                unset($data[$key]); 
                            }else if(in_array($value->account_no,$accountsNumber)){
                                $data[$key]['opening_balance'] = $value->opening_balance - $eliOpeningAmount;
                            } 
                        }
                    }

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Investment transactions listing!';
                    $result   = ['transactions' => $data];
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

    /**
     * View transaction details.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewTransactionDetails(Request $request)
    {   
        $mId = $request->member_id;
        $iId = $request->investment_id;
        $tId = $request->transaction_id;
        $member = Member::where('member_id',$mId)->where('is_block',0)->count();
        
        try {
            
            if ( $member > 0) {
                $token = md5($mId);
                if($token == $request->token){
                    $investPlan =  Memberinvestments::select('plan_id','deposite_amount','due_amount')->where('id',$iId)->first();
                    if($investPlan->plan_id == 1){
                        $data['tDetails'] = SavingAccountTranscation::where('id',$tId)->first(); 
                        $mId = SavingAccount::select('member_investments_id','member_id')->where('account_no',$data['tDetails']->account_no)->first(); 
                        $aId = Memberinvestments::select('associate_id')->where('id',$mId->member_investments_id)->first(); 
                        $data['tDetails']['member_id'] = $mId->member_id;
                        $data['tDetails']['associate_id'] = $aId->associate_id;

                        if($data['tDetails']->amount == ''){
                            $data['tDetails']['amount'] = $data['tDetails']->deposit;
                        }

                    }else{
                        $data['tDetails'] = Daybook::where('id',$tId)->first();  
                    }
                    $memberDetail = Member::find($data['tDetails']->member_id);  
                    $associateDetail = Member::find($data['tDetails']->associate_id); 

                    $data['tDetails']['deo_amount'] = $investPlan->deposite_amount;
                    $data['tDetails']['user_name'] = $memberDetail->first_name.' '.$memberDetail->last_name;
                    $data['tDetails']['associate_name'] = $associateDetail->first_name.' '.$associateDetail->last_name;
                    $data['tDetails']['associate_code'] = $associateDetail->associate_no;
                    $data['tDetails']['plan_id'] = $investPlan->plan_id;

                    if($investPlan->due_amount != ''){
                        $data['tDetails']['due_amount'] = $investPlan->due_amount;    
                    }else{
                        $data['tDetails']['due_amount'] = 0;
                    }
                    

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Transaction details!';
                    $result   = ['transactionDetail' => $data];
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

    /**
     * View member details.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function memberDetails(Request $request)
    {   
        $mId = $request->member_id;
        $member = Member::where('member_id',$mId)->where('is_block',0)->count();
        $mDetails = Member::select('id')->where('member_id',$mId)->where('is_block',0)->first();
        $data['account_numbers'] = array();
            
        try {
            if ( $member > 0) {
                $token = md5($mId);
                if($token == $request->token){
                    $mInvestments =  Memberinvestments::select('account_number')->where('member_id',$mDetails->id)->get();
                    $data['memberDetail'] = Member::where('id',$mDetails->id)->first();
                   // echo "<pre>"; print_r($data['memberDetail']); die;
                    $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id',$mDetails->id)->first();
                    $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$mDetails->id)->first();
                    $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id',$mDetails->id)->first();

                    foreach ($mInvestments as $key => $mInvestment) {
                        $data['account_numbers'][$key] = $mInvestment->account_number;               
                    }
                    if($data['memberDetail']->photo){
                        $data['memberDetail']['imageurl'] = 'http://my.samraddhbestwin.com/asset/profile/member_avatar/'.$data['memberDetail']->photo;      
                    }else{
                        $data['memberDetail']['imageurl'] = 'http://my.samraddhbestwin.com/asset/images/no-image.png';
                    }

                    if($data['memberDetail']->signature){
                        $data['memberDetail']['signatureurl'] = 'http://my.samraddhbestwin.com/asset/profile/member_avatar/'.$data['memberDetail']->signature;     
                    }else{
                        $data['memberDetail']['signatureurl'] = 'http://my.samraddhbestwin.com/asset/images/no-image.png';
                    }
                    
                    if($data['memberDetail']->gender==0){
                        $data['memberDetail']['gender'] = 'Female';
                    }elseif ($data['memberDetail']->gender==1) {
                        $data['memberDetail']['gender'] = 'Male';
                    }

                    if($data['memberDetail']->marital_status==0){
                        $data['memberDetail']['marital_status'] = 'Un Married';
                    }elseif ($data['memberDetail']->marital_status==1) {
                        $data['memberDetail']['marital_status'] = 'Married';
                    }

                    $data['memberDetail']['occupation_id'] = getOccupationName($data['memberDetail']->occupation_id);

                    if($data['memberDetail']->religion_id > 0) {
                        $data['memberDetail']['religion_id'] = getReligionName($data['memberDetail']->religion_id);
                    }

                    if($data['memberDetail']->special_category_id>0) { 
                        $data['memberDetail']['special_category_id'] = getSpecialCategoryName($data['memberDetail']->special_category_id);
                    }else {
                        $data['memberDetail']['special_category_id'] = 'General Category';
                    }

                    if($data['memberDetail']->status==1) 
                        $data['memberDetail']['status'] = 'Active';
                    else {
                       $data['memberDetail']['status'] = 'Inactive';
                    }

                    if($data['nomineeDetail']->is_minor==1) 
                        $data['nomineeDetail']['is_minor'] = 'Yes';
                    else {
                       $data['nomineeDetail']['is_minor'] = 'No';
                    } 

                    if($data['nomineeDetail']->status==1) 
                        $data['nomineeDetail']['status'] = 'Active';
                    else {
                       $data['nomineeDetail']['status'] = 'Inactive';
                    }
                        
                    if($data['nomineeDetail']->relation > 0) { 
                        $data['nomineeDetail']['relation'] = getRelationsName($data['nomineeDetail']->relation);  
                    }
                    
                    if($data['nomineeDetail']->gender==0){
                        $data['nomineeDetail']['gender'] = 'Female';
                    }elseif ($data['nomineeDetail']->gender==1) {
                        $data['nomineeDetail']['gender'] = 'Male';
                    }

                    if($data['idProofDetail']->status==1) 
                        $data['idProofDetail']['status'] = 'Active';
                    else {
                       $data['idProofDetail']['status'] = 'Inactive';
                    }

                    $data['idProofDetail']['first_id_type_id'] = getIdProofName($data['idProofDetail']->first_id_type_id);
                    
                    $data['idProofDetail']['second_id_type_id'] = getIdProofName($data['idProofDetail']->second_id_type_id);

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Member details!';
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
}
