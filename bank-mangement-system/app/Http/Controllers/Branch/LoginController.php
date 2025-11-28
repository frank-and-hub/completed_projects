<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\User;
use App\Models\Branch;
use App\Models\Settings;
use App\Models\IpAddresses;
use Carbon\Carbon;
use Session;
use App\Services\Email;

class LoginController extends Controller
{
    public function __construct()
    {

    }

    public function login()
    {
		$data['title']='Login';
		if(Auth::user()){
			return redirect()->intended('branch/dashboard');
		}else{
	        return view('/auth/branch/login', $data);
		}
    }

        
    /*public function submitlogin(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'branch' => 'required|integer',
            'password' => 'required'
        ]);
        if($validator->fails()) {
            // adding an extra field 'error'...
            $validator->errors()->add('error', 'true');
            return response()->json($validator->errors());
        }
        if ( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() ) {
	        $email = User::find( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() )->email;
        } else {
        	$email = '';
	        Session::flash('alert', 'Oops! You have entered invalid credentials');
	        return back()->withErrors('message', 'Oops! You have entered invalid credentials');
        }

    	if ( Auth::attempt(['email' => $email,'password' => $request->password,'role_id' => 3]) ) {
		  
    		if ( Branch::where('manager_id', Auth::user()->id)->pluck('status')->first() == 0 ) {
			    Auth::guard()->logout();
			    session()->forget('fakey');
			    session()->flash('alert', 'Oops! Branch is Delllllactivate, Please contact to Admin!');
			    return back()->withErrors('message', 'Oops! Branch is Deactivate, Please contact to Admin');
		    }

        	$ip_address = user_ip();
    		
        	$user=User::find(Auth::user()->id);

        	$set=$data['set']=Settings::first();
        	if($ip_address!=$user->ip_address & $set['email_notify']==1)
            {
    			send_email($user->email, $user->username, 'Suspicious Login Attempt', 'Sorry your account was just accessed from an unknown IP address<br> ' .$ip_address. '<br>If this was you, please you can ignore this message or reset your account password.');
        	}
	        $user->last_login=Carbon::now();
	      //  $user->ip_address=$ip_address;
            $user->save();
            Session::put('fakey');
            return redirect('branch/dashboard');
        } else {
        	return back()->withErrors('message', 'Oops! You have entered invalid credentials');
        }

    }*/

    public function submitlogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch' => 'required|integer',
            'password' => 'required'
        ]); 
        $sendToMember = new Email();
         
        if($validator->fails()) {
            // adding an extra field 'error'...
            $validator->errors()->add('error', 'true');
            return response()->json($validator->errors());
        }
        if ( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() ) {
            $email = User::find( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() )->email;
        } else {
            $email = '';
            //Session::flash('alert', 'Oops! You have entered invalid credentials');
            return Response::json(['view' => 'Oops! You have entered invalid credentials','msg_type'=>'error']);
            //return back()->withErrors('message', 'Oops! You have entered invalid credentials');
        }
        
        if ( Auth::attempt(['email' => $email,'password' => $request->password,'role_id' => 3]) ) {
            
           $status = Branch::where('manager_id', Auth::user()->id)->pluck('status')->first();
          
            if ($status == 0 ) {
                Auth::guard()->logout();
                session()->forget('fakey');
                //session()->flash('alert', 'Oops! Branch is Deactivate, Please contact to Admin!');
                return Response::json(['view' => 'Oops! Branch is Deactivate, Please contact to Admin!','msg_type'=>'error']);
                //return back()->withErrors('message', 'Oops! Branch is Deactivate, Please contact to Admin');
            }

            $ip_address = user_ip();
            
            $user=User::find(Auth::user()->id);
            $branch = array(3211,3212,1011,5961);
            if(!in_array($request->branch,$branch))
            {
                if(empty($request->branchstatus) || $request->branchstatus != "loginTrue"){
                    $token = User::select('branch_token')->find( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first())->branch_token;
                    
                    if(!empty($token) && strlen($token) == 9 ){          
                        Auth::guard()->logout();               
                        return Response::json(['view' => 'Already login!','msg_type'=>'exist','otp' => 3]);

                        }
                    }
            }

            $set=$data['set']=Settings::first();
            if($ip_address!=$user->ip_address & $set['email_notify']==1)
            {
                send_email($user->email, $user->username, 'Suspicious Login Attempt', 'Sorry your account was just accessed from an unknown IP address<br> ' .$ip_address. '<br>If this was you, please you can ignore this message or reset your account password.');
            }

            $generator = "135792468"; 
            $otp = ""; 
            for ($i = 1; $i <= 4; $i++) { 
                $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
            } 
           // $otp='1234';
            $otpPermission = getUserBranchOtpPermission(Auth::user()->username);

            if($otpPermission->otp_login == 0){
                $getPhoneNumner=Branch::select(['phone','email'])->where('name',Auth::user()->username)->first();
                //$getPhoneNumner = getUserBranchPhoneNumber(Auth::user()->username);
                
                $contacts = $getPhoneNumner->phone;
                $adminUser = getUserDetail('admin');
                $superAdmin = $adminUser->mobile_number;
                $AdminEmail = $adminUser->email;

                $sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
                $api_key = env('SMS_API_KEY', '26059BB05DCA39');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
                $response = curl_exec($ch);
                curl_close($ch);

                // $sms_text = 'Your OTP is '.urlencode($otp);
                // $api_key = env('SMS_API_KEY', '26059BB05DCA39');
                // $ch = curl_init();
                // curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                // curl_setopt($ch, CURLOPT_POST, 1);
                
                // curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $superAdmin . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
                // $response = curl_exec($ch);
                // curl_close($ch);
                $expemail = explode(' ',$getPhoneNumner->email);
                $mails = str_replace(',', '', $expemail[0]);

                $Bemail = strtolower(trim($mails));
                //$sendToMember->sendOtpEmail($Bemail,$otp);
                

                $hashed = md5($otp);
                $user->otp = $hashed;
                $user->varifiy_time=date("H:i:s");
                $user->last_login=Carbon::now();
                //$user->ip_address=$ip_address;
                $user->is_varified=1;
                $user->save();
                $fileName = Carbon::now()->format('Y_m_d_u');

                Session::put('fakey');
                Session::put('pnumber',$contacts);
                Session::put('uId',Auth::user()->id);
                Session::put('usern',Auth::user()->username);
                Session::put('_fileName',$fileName);


                Auth::guard()->logout();
                session()->forget('fakey');
                session()->flash('message', 'Just Logged Out!');

                //return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'branch' => $request->branch,'uId' => Session::get('uId'),'password' => $request->password,'msg_type'=>'success' ,'otp' => 0]);    

                return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'uId' => Session::get('uId'),'msg_type'=>'success' ,'otp' => 0]);
            }else{
                
                //Session::put('usern',Auth::user()->username);
                $adminUser = getUserDetail('admin');
                $AdminEmail = $adminUser->email;
                //$username = Session::get('usern').' Branch';
                $array = array("user" => Auth::user()->username.' Branch',"time" => date('d-m-Y H:i:s'));
                // $sendToMember->sendLoginEmail($AdminEmail,$array);
                $token = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 9);
                $user->branch_token = $token;
                $user->save();
                Session::put('usern',Auth::user()->username);
                Session::put('uId',Auth::user()->id);
                Session::put('branch_token',$token);
                return Response::json(['msg_type'=>'success','otp' => 1]);
                //return redirect('branch/dashboard');
            }
        } else {
            return Response::json(['view' => 'Oops! You have entered invalid credentials','msg_type'=>'error']); 
            //return back()->withErrors('message', 'Oops! You have entered invalid credentials');
        }

    }

    public function otpvarified(Request $request)
    {   
        $sendToMember = new Email();

        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer',
        ]);
        
        if($validator->fails()) {
            // adding an extra field 'error'...
            $validator->errors()->add('error', 'true');
            return response()->json($validator->errors());
        }
        $hashed = md5($request->otp);
        $adminUser = getUserDetail('admin');
        $AdminEmail = $adminUser->email;

        if ( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() ) {
            $email = User::find( Branch::where('branch_code', $request->branch)->pluck('manager_id')->first() )->email;
        }

        if ( Auth::attempt(['email' => $email,'password' => $request->password,'role_id' => 3,'otp' => $hashed]) ) {
            
            $token = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 9);

            /*$cTime=time();
            $lTime = Auth::user()->varifiy_time;
            $diff = $cTime - $lTime;
            $mDiff = date('i', $diff);*/

            $user=User::find(Auth::user()->id);
            $user->last_login=Carbon::now();
            $user->is_varified=0;
            $user->branch_token = $token;
            $user->save();
            Session::put('fakey');
            Session::put('branch_token',$token);
            $username = Session::get('usern').' Branch';
            $array = array("user" => $username,"time" => date('d-m-Y H:i:s'));
            // $sendToMember->sendLoginEmail($AdminEmail,$array);
            return Response::json(['view' => 'Successfully login!','msg_type'=>'success']); 
            //return redirect('branch/dashboard');
        } else {
            return Response::json(['view' => 'Oops! Wrong OTP','msg_type'=>'error']); 
            //return back()->withErrors('message', 'Oops! You have entered invalid credentials');
        }

    }

    public function resendOtp(Request $request)
    {
        $sendToMember = new Email();
        $generator = "135792468"; 
        $otp = ""; 
        for ($i = 1; $i <= 4; $i++) { 
            $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
        } 
        //$otp='1234';
        $usern = Session::get('usern');
        $detail = Branch::select(['phone','email'])->where('name', $usern)->first();
        $contacts = $request->pNumber;
        $adminUser = getUserDetail('admin');
        $superAdmin = $adminUser->mobile_number;
        $AdminEmail = $adminUser->email;
        
        $sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
        $api_key = env('SMS_API_KEY', '26059BB05DCA39');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,  env('SMS_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
        $response = curl_exec($ch);
        curl_close($ch);

        // $sms_text = 'Your OTP is '.urlencode($otp);
        // $api_key = env('SMS_API_KEY', '26059BB05DCA39');
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $superAdmin . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
        // $response = curl_exec($ch);
        // curl_close($ch);

        $expemail = explode(' ',$detail->email);
        $mails = str_replace(',', '', $expemail[0]);

       $Bemail = strtolower(trim($mails));
        

        //$sendToMember->sendOtpEmail($Bemail,$otp);
        

        $user=User::find($request->uId);
        $hashed = md5($otp);
        $user->otp=$hashed;
        $user->varifiy_time=date("H:i:s");
        $user->is_varified=1;
        $user->save();
        if($response = true){
            return Response::json(['view' => 'Successfully send otp!','msg_type'=>'success' ]); 
        } else {
            return Response::json(['view' => 'Oops! Something went wrong!','msg_type'=>'error']); 
        }

    }

}
