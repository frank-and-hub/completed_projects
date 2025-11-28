<?php

namespace App\Services;

class Sms
{

    public function sendSms( $phoneNumbers = array(), $message = null, $templateId ) {
		
		try{
			if (count($phoneNumbers) > 0 && $message) {
				$contacts = implode(',', $phoneNumbers);
	 
				$sms_text = urlencode($message);
	 
				$api_key = env('SMS_API_KEY', '26059BB05DCA39');
	 
				$ch = curl_init();
				// curl_setopt($ch, CURLOPT_URL, "https://sms.kutility.com/app/smsapi/index.php");
				curl_setopt($ch, CURLOPT_URL, env('SMS_URL'));

				
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POST, 1);
	 
				curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=".$templateId);
				$response = curl_exec($ch);
				if ($response === false) {
					
					throw new \Exception("Curl error: " . curl_error($ch));
				}
				curl_close($ch);
				error_log("API Response: " . $response);
	 
			} else {
				throw new \Exception('Contact Number Not Found!');
			}
		}
		catch(\Exception $error)
		{
			error_log("\Exception: " . $error->getMessage());
		}
	     
	  // return $response;
		 return false;
    }
}
