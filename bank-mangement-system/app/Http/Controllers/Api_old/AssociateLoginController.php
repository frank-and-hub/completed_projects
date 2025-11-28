<?php
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;

class AssociateLoginController extends Controller
{
    public function __construct()
    {

    }
        
    /**
     * Send otp to Associate  mobile number.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */      
    public function sendOtp(Request $request)
    {        
        try {

             $member = Member::where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->count();
            $mDetails = Member::select('id','mobile_no','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();


        	if ( $member > 0) {
                $token = md5($request->associate_no);
                $generator = "1357902468"; 
                $otp = ""; 
                for ($i = 1; $i <= 4; $i++) { 
                    $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
                }
                //------ static---

                $otp ='123456'; 


              /*  $contacts = $mDetails->mobile_no;
                $sms_text = urlencode($otp);
                $api_key = env('SMS_API_KEY', '26059BB05DCA39');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
                $response = curl_exec($ch);
                curl_close($ch);*/

                $member=Member::find($mDetails->id);
                $member->associate_otp=$otp;
                $member->associate_varifiy_time=time();
                $member->associate_is_varified=1;
                $member->save();

                $status   = "Success";
                $code     = 200;
                $messages = 'OTP send successfully!';
                $result   = $otp;
                $associate_status=$mDetails->associate_app_status;
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            }else{
                 $member = Member::where('associate_no',$request->associate_no)->first();
                 $msg = 'Associate id not found!';
                 if($member)
                 {
                    if($member->is_block==1 || $member->associate_status==0)
                     {
                        $msg='Associate code is inactive. Please contact administrator';
                     }
                 }
                 

                $status = "Error";
                $code = 201;
                $messages = $msg;
                $result = '';
                $token = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $token = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
        }
    }

    /**
     * Verify OTP.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function otpVarified(Request $request)
    {       
        
        try {

            $member = Member::where('associate_no',$request->associate_no)->where('associate_otp',$request->otp)->where('is_block',0)->where('associate_status',1)->count();

            $mDetails = Member::select('id','associate_varifiy_time','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_otp',$request->otp)->where('is_block',0)->where('associate_status',1)->first();


            if ( $member > 0) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    $cTime=time();
                    $lTime = $mDetails->associate_varifiy_time;
                    $diff = $cTime - $lTime;
                    $mDiff = date('i', $diff);

                    $member=Member::find($mDetails->id);
                    $member->is_varified=0;
                    $member->save();

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'OTP verified successfully!';
                    $result   = '';
                    $associate_status=$mDetails->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);

                    /*if($mDiff > 1){
                        $status = "Error";
                        $code = 500;
                        $messages = 'Invalid otp!';
                        $result = '';
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }else{
                        $member=Member::find($mDetails->id);
                        $member->is_varified=0;
                        $member->save();

                        $status   = "Success";
                        $code     = 201;
                        $messages = 'OTP varified successfully!';
                        $result   = ['otp' => $otp];
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);        
                    }*/
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=9;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }
            }else{
                $member = Member::where('associate_no',$request->associate_no)->first();
                 $msg = 'Associate id not found!';
                 if($member)
                 { 
                     if($member->associate_otp!=$request->otp)
                     {
                         $msg = 'Invalid OTP!';
                     }
                     if($member->is_block==1 || $member->associate_status==0)
                     {
                        $msg='Associate code is inactive. Please contact administrator';
                     }
                 }

                $status = "Error";
                $code = 201;
                $messages = $msg;
                $result = '';
                $associate_status=9;
                $token='';
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            }
            
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
                $token='';
            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
        }        

    }

    /**
     * Set upi code.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setUpiCode(Request $request)
    {
        
        try {

            $upi = $request->upi;
            $member = Member::where('associate_no',$request->associate_no)->where('is_block',0)->where('associate_status',1)->count();
            $mDetails = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('is_block',0)->where('associate_status',1)->first();


            if ( $member > 0) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    if($upi!='')
                    {
                        $member=Member::find($mDetails->id);
                        $member->associate_upi = $upi;
                        $member->save();

                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Security pin has been set successfully!';
                        $result   = '';
                        $associate_status=$mDetails->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                    }
                    else
                    {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter pin';
                        $result = '';
                        $associate_status=9;
                        return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);  
                    }
                    
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=9;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }
            }else{
                $member = Member::where('associate_no',$request->associate_no)->first();
                 $msg = 'Associate id not found!';
                 if($member)
                 {
                    if($member->is_block==1 || $member->associate_status==0)
                     {
                        $msg='Associate code is inactive. Please contact administrator';
                     }
                 }
                 

                $status = "Error";
                $code = 201;
                $messages = $msg;
                $result = '';
                $associate_status=9;
                $token='';
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = ['error' => $e->getMessage()];
            $result = '';
            $associate_status=9;
            $token='';
            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
        }        

    }

    /**
     * Login with upi code.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginWithUpi(Request $request)
    {
                $data = array();
                    $data['id'] = 0;
                    $data['name'] = '';
                    $data['associate_number'] = '';
                    $data['mobile_no'] = '';
                    $data['profile_imge']  = '';                    
                    $data['branch_code'] = '';
                    $data['branch_name'] = '';
                    $data['senior_associate_code']='';
                    $data['senior_associate_name']='';
        
        
        try {

            $member = Member::where('associate_no',$request->associate_no)->where('associate_upi',$request->upi)->where('is_block',0)->where('associate_status',1)->count();

            $mDetails = Member::with('branch')->where('associate_no',$request->associate_no)->where('associate_upi',$request->upi)->where('is_block',0)->where('associate_status',1)->first();


            if ( $member > 0) {
                $token = md5($request->associate_no);
                if($token == $request->token){

                    $data['id'] = $mDetails->id;
                    $data['name'] = $mDetails->first_name.' '.$mDetails->last_name;
                    $data['associate_number'] = $mDetails->associate_no;
                    $data['mobile_no'] = $mDetails->mobile_no; 
                    if($mDetails->photo){
                        $data['profile_imge']  = 'http://uat.samraddhbestwin.com/asset/profile/member_avatar/'.$mDetails->photo;  
                    }else{
                         $data['profile_imge']  = 'http://uat.samraddhbestwin.com/asset/images/no-image.png';
                    }
                    $data['branch_code'] = $mDetails['branch']->branch_code;
                        $data['branch_name'] = $mDetails['branch']->name;
                    $data['senior_associate_code']=getSeniorData($mDetails->associate_senior_id,'associate_no');
                    $data['senior_associate_name']=getSeniorData($mDetails->associate_senior_id,'first_name').' '.getSeniorData($mDetails->associate_senior_id,'last_name');
                    
                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Successfully login!';
                    $result   =  ['member' => $data];
                    $associate_status=$mDetails->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result   =  ['member' => $data];
                    $associate_status=9;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }
            }
            else
            {
                $member = Member::where('associate_no',$request->associate_no)->first();
                 $msg = 'Associate id not found!';
                 if($member)
                 {
                    if($member->is_block==1 || $member->associate_status==0)
                     {
                        $msg='Associate code is inactive. Please contact administrator';
                     }
                 }
                 

                $status = "Error";
                $code = 201;
                $messages = $msg;
                $result   =  ['member' => $data];
                $associate_status=9;
                $token='';
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = ['error' => $e->getMessage()];
            $result   =  ['member' => $data];
            $associate_status=9;
            $token='';
            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
        }        

    }




    public function user_profile(Request $request)
    {
        try {

            $member = Member::where('associate_no',$request->associate_no)->where('is_block',0)->where('associate_status',1)->count();
            if ( $member > 0) {
                $token = md5($request->associate_no);
                if($token == $request->token){
                    $data = array() ;
                             $memberData =$mDetails = \App\Models\Member::where('associate_no',$request->associate_no)->first();
                             $id=$memberData->id;
                            $bankDetail= \App\Models\MemberBankDetail::where('member_id',$id)->first();

                             $data['bankDetail']['id']=0;
                             $data['bankDetail']['member_id']=0;
                             $data['bankDetail']['bank_name']='';
                             $data['bankDetail']['branch_name']='';
                             $data['bankDetail']['account_no']='';
                             $data['bankDetail']['ifsc_code']='';
                             $data['bankDetail']['address']='';  
                             if($bankDetail)
                             {
                               // print_r($data['bankDetail']);die;
                                 $data['bankDetail']['id']=$bankDetail->id;
                                 $data['bankDetail']['member_id']=$bankDetail->member_id;
                                 $data['bankDetail']['bank_name']=$bankDetail->bank_name;
                                 $data['bankDetail']['branch_name']=$bankDetail->branch_name;
                                 $data['bankDetail']['account_no']=$bankDetail->account_no;
                                 $data['bankDetail']['ifsc_code']=$bankDetail->ifsc_code;
                                 $data['bankDetail']['address']=$bankDetail->address; 
                             }
                             $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$id)->first();
                             $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id',$id)->first();

                            $data['member_form_information']['form_no']=$memberData->associate_form_no;
                            $data['member_form_information']['join_date']=date("d/m/Y", strtotime($memberData->associate_join_date));
                            $data['member_form_information']['member_id']=$memberData->member_id;
                            $data['member_form_information']['branch_mi']=$memberData->branch_mi;

                            $data['member_form_information']['associate_no']=$memberData->associate_no;
                            $data['member_form_information']['carder']=getCarderName($memberData->current_carder_id);

                            $data['associate_details']['associate_code']=getSeniorData($memberData->associate_senior_id,'associate_no');
                            $data['associate_details']['associate_name']=getSeniorData($memberData->associate_senior_id,'first_name').' '.getSeniorData($memberData->associate_senior_id,'last_name');
                            $data['associate_details']['mobile_no']=getSeniorData($memberData->associate_senior_id,'mobile_no');
                             $data['associate_details']['carder']='';
                            if($data['associate_details']['associate_code']!='9999999')
                            {
                                $data['associate_details']['carder']=getCarderName(getSeniorData($memberData->associate_senior_id,'current_carder_id'));
                            }

                            $data['personal_information']['first_name']=$memberData->first_name;
                            $data['personal_information']['last_name']=$memberData->last_name;
                            $data['personal_information']['email']=$memberData->email;
                            $data['personal_information']['mobile_no']=$memberData->mobile_no;
                            $data['personal_information']['age']=$memberData->age;
                            $data['personal_information']['dob']=date("d/m/Y", strtotime($memberData->dob));
                            if($memberData->gender==0){
                                $data['personal_information']['gender'] = 'Female';
                            }elseif ($memberData->gender==1) {
                                $data['personal_information']['gender'] = 'Male';
                            }
                            $data['personal_information']['occupation_id'] = getOccupationName($memberData->occupation_id);
                            $data['personal_information']['annual_income']=number_format($memberData->annual_income, 2, '.', ',');
                            $data['personal_information']['mother_name']=$memberData->mother_name;
                            $data['personal_information']['father_husband']=$memberData->father_husband;
                            if($memberData->marital_status==0){
                                $data['personal_information']['marital_status'] = 'Un Married';
                            }elseif ($memberData->marital_status==1) {
                                $data['personal_information']['marital_status'] = 'Married';
                            }
                            if($memberData->anniversary_date)
                            {
                              $data['personal_information']['anniversary_date']=date("d/m/Y", strtotime($memberData->anniversary_date));  
                            }
                            else
                            {
                               $data['personal_information']['anniversary_date']=''; 
                            }
                            if($memberData->religion_id>0)
                            {
                              $data['personal_information']['religion']=getReligionName($memberData->religion_id);  
                            }
                            else
                            {
                               $data['personal_information']['religion']=''; 
                            }
                            if($memberData->special_category_id>0)
                            {
                              $data['personal_information']['special_category']=getSpecialCategoryName($memberData->special_category_id);  
                            }
                            else
                            {
                               $data['personal_information']['special_category']='General Category'; 
                            }
                            if($memberData->status==1) 
                                $data['personal_information']['status'] = 'Active';
                            else {
                               $data['personal_information']['status'] = 'Inactive';
                            }  
                            $data['personal_information']['address'] = $memberData->address;
                            $data['personal_information']['state'] = getStateName($memberData->state_id);
                            $data['personal_information']['district'] = getDistrictName($memberData->district_id);
                            $data['personal_information']['city'] = getCityName($memberData->city_id);
                            $data['personal_information']['village'] = $memberData->village;
                            $data['personal_information']['pin_code'] = $memberData->pin_code;

                              
                            if($memberData->photo){
                                $data['profile_imge']  = 'http://uat.samraddhbestwin.com/asset/profile/member_avatar/'.$memberData->photo;      
                            }else{
                                $data['profile_imge']  = 'http://uat.samraddhbestwin.com/asset/images/no-image.png';
                            }

                            if($memberData->signature){
                                $data['signatureurl'] = 'http://uat.samraddhbestwin.com/asset/profile/member_signature/'.$memberData->signature;     
                            }else{
                                $data['signatureurl']  = 'http://uat.samraddhbestwin.com/asset/images/no-image.png';
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

                            if($data['nomineeDetail']->dob)
                            {
                              $data['nomineeDetail']['dob']=date("d/m/Y", strtotime($data['nomineeDetail']->dob));  
                            }
                            else
                            {
                               $data['nomineeDetail']['dob']=''; 
                            }
                            

                            if($data['idProofDetail']->status==1) 
                                $data['idProofDetail']['status'] = 'Active';
                            else {
                               $data['idProofDetail']['status'] = 'Inactive';
                            }

                            $data['idProofDetail']['first_id_type_id'] = getIdProofName($data['idProofDetail']->first_id_type_id);
                            
                            $data['idProofDetail']['second_id_type_id'] = getIdProofName($data['idProofDetail']->second_id_type_id);

                            $dependentDetail = \App\Models\AssociateDependent::where('member_id',$id)->get();
                            if(count($dependentDetail)>0) 
                            {
                            foreach ($dependentDetail as $key => $val) {
                                    $data['dependentDetail'][$key]['id'] = $val->id;
                                    $data['dependentDetail'][$key]['name'] = $val->name;
                                    if($val->dependent_type==1)
                                        $dependenttype='Fully';
                                    else
                                        $dependenttype='Partially';

                                    $data['dependentDetail'][$key]['dependent_type'] = $dependenttype;
                                    if($val->gender==1)
                                        $gender='Male';
                                    else
                                        $gender='Female';
                                    $data['dependentDetail'][$key]['gender'] = $gender;
                                    if($val->relation>0) 
                                        $relation=getRelationsName($val->relation);
                                    else
                                        $relation='';
                                    $data['dependentDetail'][$key]['relation'] = $relation;
                                    if($val->marital_status==1)
                                        $marital_status='Married';
                                    else
                                        $marital_status='Un Married';
                                    $data['dependentDetail'][$key]['marital_status'] = $marital_status;
                                    if($val->living_with_associate==1)
                                        $living_with_associate='Yes';
                                    else
                                        $living_with_associate='No';
                                    $data['dependentDetail'][$key]['living_with_associate'] = $living_with_associate;

                                    $data['dependentDetail'][$key]['monthly_income'] =number_format($val->monthly_income, 2, '.', ',');

                                }
                            }
                            else
                            {
                                $data['dependentDetail']=array();
                            }
                    
                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Successfully login!';
                    $result   =  ['user_profile' => $data];
                    $associate_status=$mDetails->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result   =  '';
                    $associate_status=9;
                    return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
                }
            }else{
                $member = Member::where('associate_no',$request->associate_no)->first();
                 $msg = 'Associate id not found!';
                 if($member)
                 {
                    if($member->is_block==1 || $member->associate_status==0)
                     {
                        $msg='Associate code is inactive. Please contact administrator';
                     }
                 }
                 

                $status = "Error";
                $code = 201;
                $messages = $msg;
                $result   = '';
                $associate_status=9;
                $token='';
                return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = ['error' => $e->getMessage()];
            $result   = '';
            $associate_status=9;
            $token='';
            return response()->json(compact('status', 'code', 'messages', 'result','token','associate_status'), $code);
        }        

    }
}
