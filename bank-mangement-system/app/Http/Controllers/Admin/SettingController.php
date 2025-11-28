<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Settings;
use App\Models\Admin;
use App\Models\Etemplate;
use Carbon\Carbon;
use Session;


class SettingController extends Controller
{

    public function Settings()
    {
        $data['title']='General settings';
        return view('admin.settings.basic-setting', $data);
    }     
    
    public function Email()
    {
        $data['title']='Email settings';
        $data['val']=Etemplate::first();
        return view('admin.settings.email', $data);
    } 

    public function EmailUpdate(Request $request)
    {
        $data = Etemplate::findOrFail(1);
        $data->esender=$request->sender;
        $data->emessage=$request->message;
        $res=$data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }      
    
    public function Account()
    {
        $data['title']='Change account details';
        $data['val']= Admin::find(Auth::user()->id);
        return view('templates.admin.settings.account', $data);
    } 

	public function AccountUpdate(Request $request) {
		//Old Code
		/*
		$data = Admin::find( $request->id );
        $data->username=$request->username;
        $data->password=Hash::make($request->password);
        $res=$data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
		*/
		
		$otp_get = Session::get('otp_pass');
		
		if($request->otp == $otp_get){
			$data = Admin::find( $request->id );
			$data->username=$request->username;
			$data->password=Hash::make($request->password);
			//$res = 1;
			$res = $data->save();
			
			if ($res) {
				Session::forget('otp_pass');
				Admin::where('id', $data->id)->update(['logged_in' => 0,'user_token'=>null]);
				$output = array('view' => 'Update was Successful','msg_type'=>'success');
			} else {
				$output = array('view' => 'An error occured','msg_type'=>'error');
			}
		} 
		else{
			$output = array('view' => 'Oops! You have entered invalid otp','msg_type'=>'error');
		}
		echo json_encode($output);
    } 
    
    public function Sms()
    {
        $data['title']='Sms settings';
        $data['val']=Etemplate::first();
        return view('admin.settings.sms', $data);
    } 

    public function SmsUpdate(Request $request)
    {
        $data = Etemplate::findOrFail(1);
        $data->twilio_sid=$request->twilio_sid;
        $data->twilio_auth=$request->twilio_auth;
        $data->twilio_number=$request->twilio_number;
        $res=$data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }      
    
    public function SettingsUpdate(Request $request)
    {
        $data = Settings::findOrFail(1);
        $data->site_name=$request->site_name;
        $data->tawk_id=$request->tawk_id;
        $data->email=$request->email;
        $data->mobile=$request->mobile;
        $data->title=$request->title;
        $data->transfer_charge=$request->transfer_charge;
        $data->transfer_chargex=$request->transfer_chargex;
        $data->balance_reg=$request->bal;
        $data->upgrade_fee=$request->upgrade_fee;
        $data->loan_interest=$request->loan_interest;
        $data->saving_interest=$request->saving_interest;
        $data->saving_charge=$request->saving_charge;
        $data->withdraw_charge=$request->withdraw_charge;
        $data->merchant_charge=$request->merchant_charge;
        $data->collateral_percent=$request->collateral_percent;
        $data->site_desc=$request->site_desc;
        $data->address=$request->address;
        $data->api=$request->api;
        $data->gradient1=$request->gradient1;
        $data->gradient2=$request->gradient2;
        if(empty($request->kyc)){
            $data->kyc=0;	
        }else{
            $data->kyc=$request->kyc;
        }    
        if(empty($request->email_activation)){
            $data->email_verification=0;	
        }else{
            $data->email_verification=$request->email_activation;
        }       
        if(empty($request->sms_activation)){
            $data->sms_verification=0;	
        }else{
            $data->sms_verification=$request->sms_activation;
        }        
        if(empty($request->email_notify)){
            $data->email_notify=0;	
        }else{
            $data->email_notify=$request->email_notify;
        }  
        if(empty($request->sms_notify)){
            $data->sms_notify=0;	
        }else{
            $data->sms_notify=$request->sms_notify;
        }        
        if(empty($request->registration)){
            $data->registration=0;	
        }else{
            $data->registration=$request->registration;
        }           
        if(empty($request->loan)){
            $data->loan=0;	
        }else{
            $data->loan=$request->loan;
        }        
        if(empty($request->save)){
            $data->save=0;	
        }else{
            $data->save=$request->save;
        }
        if(empty($request->auto)){
            $data->auto=0;	
        }else{
            $data->auto=$request->auto;
        }           
        if(empty($request->asset)){
            $data->asset=0;	
        }else{
            $data->asset=$request->asset;
        }           
        if(empty($request->merchant)){
            $data->merchant=0;	
        }else{
            $data->merchant=$request->merchant;
        }    
        $res=$data->save();
        if ($res) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    } 
	
	public function viewotp(Request $request){
        $data = Admin::find($request->id);
        $data->username=$request->username;
		
		$contacts = $data->mobile_number;
		$id = $data->id;
		
		Session::forget('otp_pass');
		$otp_set = Session::put('otp_pass', rand(1000,9999));
		$otp_get = Session::get('otp_pass');
		
		$sms_text = 'Your OTP is '.urlencode($otp_get);
		$api_key = env('SMS_API_KEY', '26059BB05DCA39');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
		$response = curl_exec($ch);
		curl_close($ch);
		
		//dd($res);
		//Auth::logoutOtherDevices($request->password);
		//Auth::logout();
		$output = array('msg_type'=>'success' ,'otp' => $otp_get);
		echo json_encode($output);
    } 
}
