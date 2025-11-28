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

class LoginController extends Controller
{
    public function __construct()
    {

    }
        
    /**
     * Send otp to member mobile number.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */      
    public function sendOtp(Request $request)
    {   
        $member = Member::where('member_id',$request->member_id)->where('is_block',0)->where('is_block',0)->count();
        $mDetails = Member::select('id','mobile_no')->where('member_id',$request->member_id)->where('is_block',0)->where('is_block',0)->first();
        
        try {
        	if ( $member > 0) {
                $token = md5($request->member_id);
                $generator = "1357902468"; 
                $otp = ""; 
                for ($i = 1; $i <= 4; $i++) { 
                    $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
                }

                $contacts = $mDetails->mobile_no;
               
                $sms_text = 'Your OTP is '.urlencode($otp);
                $api_key = env('SMS_API_KEY', '26059BB05DCA39');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
                $response = curl_exec($ch);
                curl_close($ch);

                $member=Member::find($mDetails->id);
                $member->otp=$otp;
                $member->varifiy_time=time();
                $member->is_varified=1;
                $member->save();

                $status   = "Success";
                $code     = 200;
                $messages = 'OTP send successfully!';
                $result   = '';
                return response()->json(compact('status', 'code', 'messages', 'result','token'), $code);
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Member id not found!';
                $result = '';
                $token = '';
                return response()->json(compact('status', 'code', 'messages', 'result','token'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $token = '';
            return response()->json(compact('status', 'code', 'messages', 'result','token'), $code);
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
        $member = Member::where('member_id',$request->member_id)->where('otp',$request->otp)->where('is_block',0)->count();
        $mDetails = Member::select('id','varifiy_time')->where('member_id',$request->member_id)->where('otp',$request->otp)->where('is_block',0)->first();
        
        try {
            if ( $member > 0) {
                $token = md5($request->member_id);
                if($token == $request->token){
                    $cTime=time();
                    $lTime = $mDetails->varifiy_time;
                    $diff = $cTime - $lTime;
                    $mDiff = date('i', $diff);

                    $member=Member::find($mDetails->id);
                    $member->is_varified=0;
                    $member->save();

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'OTP verified successfully!';
                    $result   = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);

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
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Invalid OTP!';
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
     * Set upi code.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setUpiCode(Request $request)
    {
        $upi = $request->upi;
        $member = Member::where('member_id',$request->member_id)->where('is_block',0)->count();
        $mDetails = Member::select('id')->where('member_id',$request->member_id)->where('is_block',0)->first();
        try {
            if ( $member > 0) {
                $token = md5($request->member_id);
                if($token == $request->token){
                    $member=Member::find($mDetails->id);
                    $member->upi = $upi;
                    $member->save();

                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Security pin has been set successfully!';
                    $result   = '';
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
                $messages = 'Member id not found!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = ['error' => $e->getMessage()];
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
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
        $member = Member::where('member_id',$request->member_id)->where('upi',$request->upi)->where('is_block',0)->count();
        $mDetails = Member::select('id')->where('member_id',$request->member_id)->where('upi',$request->upi)->where('is_block',0)->first();
        
        try {
            if ( $member > 0) {
                $token = md5($request->member_id);
                if($token == $request->token){
                    $status   = "Success";
                    $code     = 200;
                    $messages = 'Successfully login!';
                    $result   = '';
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
                $messages = 'Member id not found!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = ['error' => $e->getMessage()];
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }        

    }
}
