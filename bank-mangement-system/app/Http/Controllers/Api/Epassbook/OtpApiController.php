<?php
namespace App\Http\Controllers\Api\Epassbook;
//die('stop app');
use DB;
use URL;
use Session;
use Carbon;
use DateTime;
use Validator; 
use App\Services\Sms; 
use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response; 
class OtpApiController extends Controller
{
    public function __construct()
    {
    }
    /**
     * payment verification API .
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function sendLoanOtp(Request $request)
    {
        $return = array();
        $input = $request->all();
        if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["type"]) && $input["type"]!="")
        {
            // Get Member ID
            $member = Member::select('id','mobile_no')->where('member_id',$input["member_id"])->first();
            if(isset($member->id)){
                $type = $input["type"];
                $contacts = $member->mobile_no;
                //$otp = rand(1000,9999);
                $generator = "1357902468"; 
                $otp = "";
                for ($i = 1; $i <= 4; $i++) { 
                    $otp .= substr($generator, (rand()%(strlen($generator))), 1); 
                }
                // $otp = generateOtp();
                
                if($type == "deposit"){
                    // $sms_text = 'Your OTP is '. urlencode($otp) . ' From SBMFA D5gKgRBriBc';
                    $sms_text = 'Your E-passbook APP login OTP is ' . urlencode($otp) . ' From Samraddh Bestwin Micro Finance Association F0BMC1IosIy';
                } 
                elseif($type == "loan"){
                    //$sms_text = 'Your Loan OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
                    // $sms_text = 'Your OTP is '. urlencode($otp) . ' From SBMFA D5gKgRBriBc';
                    $sms_text = 'Your E-passbook APP login OTP is ' . urlencode($otp) . ' From Samraddh Bestwin Micro Finance Association F0BMC1IosIy';
                } else {
                    //$sms_text = 'Your Group Loan OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
                    // $sms_text = 'Your OTP is '. urlencode($otp) . ' From SBMFA D5gKgRBriBc';
                    $sms_text = 'Your E-passbook APP login OTP is ' . urlencode($otp) . ' From Samraddh Bestwin Micro Finance Association F0BMC1IosIy';
                }


                //    $sms_text = 'Your OTP is '.urlencode($otp).' From Sammradh Bestwin Microfinance';
                // $sms_text = 'Your OTP is '.urlencode($otp).'  From SBMFA D5gKgRBriBc';
                $sms_text = 'Your E-passbook APP login OTP is ' . urlencode($otp) . ' From Samraddh Bestwin Micro Finance Association F0BMC1IosIy';
                $api_key = env('SMS_API_KEY', '26059BB05DCA39');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,env('SMS_URL'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881467782483");
                
                // curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text . "&template_id=1207168447849593359");
                curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text . "&template_id=1207170305488224944");
                
                $response = curl_exec($ch);
                curl_close($ch);

                 



                
                $return["status"] = "Success";
                $return["code"] = 200;
                $return["messages"] = "OTP send successfully!";
                $return["otp"] = $otp;
            } else {
                $return["status"] = "Error";
                $return["code"] = 201;
                $return["messages"] = "Enter Valid Member Id";
                $return["otp"] = "";
            }
        } else {
            $return["status"] = "Error";
            $return["code"] = "201";
            $return["messages"] = "Input parameter missing";
            $return["otp"] = "";
        }
        return response()->json($return);
    } 
}