<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Auth;
//use Illuminate\Support\Facades\Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\User;
use App\Models\Branch;
use App\Models\Admin;
use App\Models\IpAddresses;
use Carbon\Carbon;
use Session;
use App\Services\Email;

class AdminLoginController extends Controller
{


	public function __construct(){
		$Gset = Settings::first();
		$this->sitename = $Gset->site_name;
	}


	public function index(){

		if(Auth::guard('admin')->check() && Auth::check() ){
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Admin";
		return view('admin.index', $data);
	}

	public function authenticate(Request $request){
		if (Auth::guard('admin')->attempt([
			'username' => $request->username,
			'password' => $request->password,
			'status' => '1'
		])) {
			return redirect()->intended('admin/dashboard');
		}else{

			if (Auth::guard('admin')->attempt([
			'user_id' => $request->username,
			'password' => $request->password,
			'status' => '1'
			])) {
				return redirect()->intended('admin/dashboard');
			} else {
				return back()->with('alert', 'Oops! You have entered invalid credentials');
			}
		}
	}







	public function submitlogin(Request $request)
    {

		 $sendToMember = new Email();


        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if($validator->fails()) {
            // adding an extra field 'error'...
            $validator->errors()->add('error', 'true');
            return response()->json($validator->errors());
        }

		if (Auth::guard('admin')->attempt([
			'username' => $request->username,
			'password' => $request->password
		])) {
			if (Auth::guard('admin')->user()->status == 0 ) {
                Auth::guard('admin')->logout();
                session()->forget('fakey');
                return Response::json(['view' => 'Oops! Branch is Deactivate, Please contact to Admin!','msg_type'=>'error']);
            } else {
            	$roleid =  Auth::guard('admin')->user()->role_id;
				$contacts =  Auth::guard('admin')->user()->mobile_number;
				$email =  Auth::guard('admin')->user()->email;
				$adminUser = getUserDetail('admin');
				$superAdmin = $adminUser->mobile_number;
				$AdminEmail = $adminUser->email;

				$id = Auth::guard('admin')->user()->id;
				$role_id = Auth::guard('admin')->user()->role_id;
				if($role_id != 5){
				 if(empty($request->loginstatus) || $request->loginstatus != "loginTrue"){
				 	$token = Auth::guard('admin')->user()->user_token;
				 	if(!empty($token) && strlen($token) == 9 ){
			          return Response::json(['view' => 'Already login!','msg_type'=>'exist','otp' => 3]);
			         }else{
			          	Admin::where('id', $id)->update(['logged_in' => 1]);
			         }
				 }

				}
				// if($role_id == "1"){
				// 	return Response::json(['view' => 'Successfully login!','pnumber' => $contacts,'uId' => $id,'msg_type'=>'success' ,'otp' => 1]);
				// } else {
					$generator = "135792468";
					$otp = '';
					for ($i = 1; $i <= 4; $i++) {
						$otp .= substr($generator, (rand()%(strlen($generator))), 1);
					}
					
					// $otp='9332';
					// / $otp='1234';

					//Temporary
					
					//Comment date 14-09-2023 bcoz of sms server	
					
					
					$sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
					$api_key = env('SMS_API_KEY', '26059BB05DCA39');
					$ch = curl_init();
				
					curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
					$response = curl_exec($ch);
				
					curl_close($ch);
					//Comment date 06-09-2023 bcoz of sms server					
					 $sendToMember->sendOtpEmail($email,$otp);

					if($roleid != 1){



				        // $sendToMember->sendOtpEmail($AdminEmail,$otp);
					}


					$user=Admin::find($id);
					$hashed = md5($otp);
					$user->otp=$hashed;
					$user->varifiy_time=date("H:i:s");
					$user->is_varified=1;
					$user->save();
					$fileName = Carbon::now()->format('Y_m_d_u');
					Session::put('fakey');
					Session::put('pnumber',$contacts);
					Session::put('rid',$role_id);
					Session::put('uId',$id);
					Session::put('_fileName',$fileName);


					Auth::guard('admin')->logout();
					session()->forget('fakey');
					session()->flash('message', 'Just Logged Out!');

					//return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'branch' => $request->branch,'uId' => Session::get('uId'),'password' => $request->password,'msg_type'=>'success' ,'otp' => 0]);

					return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'uId' => Session::get('uId'),'msg_type'=>'success' ,'otp' => 0]);
			//}

			}


		}else{

			if (Auth::guard('admin')->attempt([
			'user_id' => $request->username,
			'password' => $request->password,
			'status' => '1'
			])) {


				$roleid =  Auth::guard('admin')->user()->role_id;
				$contacts =  Auth::guard('admin')->user()->mobile_number;
				$email =  Auth::guard('admin')->user()->email;
				$adminUser = getUserDetail('admin');
				$superAdmin = $adminUser->mobile_number;
				$AdminEmail = $adminUser->email;

				$id = Auth::guard('admin')->user()->id;
				$role_id = Auth::guard('admin')->user()->role_id;

				if($role_id != 5){
					 if(empty($request->loginstatus) || $request->loginstatus != "loginTrue"){

					 	$token = Auth::guard('admin')->user()->user_token;
					 	if(!empty($token) && strlen($token) == 9 ){
				          return Response::json(['view' => 'Already login!','msg_type'=>'exist','otp' => 3]);
				         }else{
				         }
					 }
				}


				if($role_id == "1"){
					return Response::json(['view' => 'Successfully login!','pnumber' => $contacts,'uId' => $id,'msg_type'=>'success' ,'otp' => 1]);
				} else {
					$generator = "135792468";
					$otp = "";
					for ($i = 1; $i <= 4; $i++) {
						$otp .= substr($generator, (rand()%(strlen($generator))), 1);
					}


					//$otp='9332';
					// $otp='1234';
					//Comment date 14-09-2023 bcoz of sms server					

					$sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
					$api_key = env('SMS_API_KEY', '26059BB05DCA39');
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_POST, 1);
					//curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881681305418");
					
					curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483"); 
					$response = curl_exec($ch);
					curl_close($ch);
					//Comment date 06-09-2023					
					//$sendToMember->sendOtpEmail($email,$otp);

					if($roleid != 1){

						/*
							$chl = curl_init();
					        curl_setopt($chl, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
					        curl_setopt($chl, CURLOPT_RETURNTRANSFER, 1);
					        curl_setopt($chl, CURLOPT_POST, 1);
					        curl_setopt($chl, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $superAdmin . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
					        $response = curl_exec($chl);
					        curl_close($chl);
				        */

				       // $sendToMember->sendOtpEmail($AdminEmail,$otp);
					}
					$user=Admin::find($id);
					$hashed = md5($otp);
					$user->otp=$hashed;
					$user->varifiy_time=date("H:i:s");
					$user->is_varified=1;
					$user->save();

					Session::put('fakey');
					Session::put('pnumber',$contacts);
					Session::put('rid',$role_id);
					Session::put('uId',$id);
					$fileName = Carbon::now()->format('Y_m_d_u');

					Session::put('_fileName',$fileName);


					Auth::guard('admin')->logout();
					session()->forget('fakey');
					session()->flash('message', 'Just Logged Out!');

					//return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'branch' => $request->branch,'uId' => Session::get('uId'),'password' => $request->password,'msg_type'=>'success' ,'otp' => 0]);

					return Response::json(['view' => 'Successfully login!','pnumber' => Session::get('pnumber'),'uId' => Session::get('uId'),'msg_type'=>'success' ,'otp' => 0]);
			}


			} else {
				return Response::json(['view' => 'Oops! You have entered invalid credentials','msg_type'=>'error']);
			}
		}

    }


	public function otpAdminvarified(Request $request)
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

        $roleid =  Session::get('rid');
		$adminUser = getUserDetail('admin');
		$AdminEmail = $adminUser->email;

		if (Auth::guard('admin')->attempt(['username' => $request->username,'password' => $request->password,'otp' => $hashed])){
			$token = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 9);
            $user=Admin::find(Auth::guard('admin')->user()->id);
            $user->is_varified=0;
            $user->user_token = $token;
            $user->save();
            Session::put('fakey');
            Session::put('token',$token);

            $array = array("user" => $request->username,"time" => date('d-m-Y H:i:s'));
            // if($roleid != 1){
            // 	$sendToMember->sendLoginEmail($AdminEmail,$array);
            // }


            return Response::json(['view' => 'Successfully login!','msg_type'=>'success']);
        }
		elseif(Auth::guard('admin')->attempt(['user_id' => $request->username,'password' => $request->password,'otp' => $hashed])) {
			$token = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 9);
			$user=Admin::find(Auth::guard('admin')->user()->id);
            $user->is_varified=0;
            $user->user_token = $token;
            $user->save();
            Session::put('fakey');
            Session::put('token',$token);
            $array = array("user" => $request->username,"time" => date('d-m-Y H:i:s'));
            // if($roleid != 1){
            // 	$sendToMember->sendLoginEmail($AdminEmail,$array);
            // }
            return Response::json(['view' => 'Successfully login!','msg_type'=>'success']);
		}
		else {
            return Response::json(['view' => 'Oops! Wrong OTP','msg_type'=>'error']);
        }

    }



	public function resendAdminotp(Request $request)
    {
    	$sendToMember = new Email();
        $generator = "135792468";
        $otp = "";
        for ($i = 1; $i <= 4; $i++) {
            $otp .= substr($generator, (rand()%(strlen($generator))), 1);
        }
		// $otp='9332';
		// $otp='1234';
        $id = Session::get('uId');
		$detail = getUserRoleDetail($id);
		$contacts = $request->pNumber;
		$adminUser = getUserDetail('admin');
		$superAdmin = $adminUser->mobile_number;
		$AdminEmail = $adminUser->email;
		$sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
        $api_key = env('SMS_API_KEY', '26059BB05DCA39');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . //$contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881681305418");
// 		$contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
	//	print_r("key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . //$contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881681305418");
		//$contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
     //  die;
        $response = curl_exec($ch);
       
        curl_close($ch);

    //    $sendToMember->sendOtpEmail($detail->email,$otp);

        if($detail->role_id != 1){


	        	// $chl = curl_init();
		        // curl_setopt($chl, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
		        // curl_setopt($chl, CURLOPT_RETURNTRANSFER, 1);
		        // curl_setopt($chl, CURLOPT_POST, 1);
		        // curl_setopt($chl, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $superAdmin . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207161519554832553");
		        // $response = curl_exec($chl);
		        // curl_close($chl);


	        //$sendToMember->sendOtpEmail($AdminEmail,$otp);
        }



        $user=Admin::find($request->uId);
        $hashed = md5($otp);
        $user->otp=$hashed;
        $user->varifiy_time=date("H:i:s");
        $user->is_varified=1;
        $user->save();
        if($response = true){
            return Response::json(['view' => 'Successfully send otp!','msg_type'=>'success']);
        } else {
            return Response::json(['view' => 'Oops! Something went wrong!','msg_type'=>'error']);
        }

    }





}
