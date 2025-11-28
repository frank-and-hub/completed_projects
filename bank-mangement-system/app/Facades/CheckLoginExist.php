<?php
namespace App\Facades;

class CheckLoginExist
{
    public function userCheck($request,$details,$deviceType)
    {
        //Check device id of current User  and new user
        try{
            $status =  !empty($details) && ($request->device_id != $details->device_id) ? $this->sendNotification($details,$request) : false;
            $details->update(['device_id' => $request->device_id,'mobile_token'=>$request->mobile_token]) ;
            
            return $status;
        }
        catch (\Exception $err)
        {
        }
      

    }

    public function sendNotification($memberDetail,$request)
    {
      
        if(!empty($memberDetail))
        {   

        
           
            // Firebase API Integration for Mobile Number Update Start
                $title = "Already Login";                            
                $body = "This Account already Used in Other Device.";
                if($memberDetail->mobile_token !='')
                {                             
                    $dataApi = array("type" => "logout", 'title' => $title, 'sound' => 'default', 'body' => $body,'id' =>$memberDetail->id);
                    $fields = array('to' => $memberDetail->mobile_token, 'data' => $dataApi);
                    $dataApi = json_encode($fields);
                    //FCM API end-point
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
                    $server_key = 'AAAAMiX2FHk:APA91bEkiW8IBU79vW-tk8KaLct1GiCmRkYc7SwQy7Els_A6lGOSiOe9ODtqeCz99RPm1LpNfINa12xJYluWQ10oSFkWxPYMGNKDWJtkYcb9owj_7EF7rmR3fmYz4QoppAy_qKo-jkso';
                    //header with content_type api key
                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization:key='.$server_key
                    );
                    //CURL request to route notification to FCM connection server (provided by Google)
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataApi);
                    $result = curl_exec($ch);
                   
                    curl_close($ch);
                    
                    //print_r($result); 
                }
                return true;
                // Firebase API Integration for Mobile Number Update End
        }
    }


    public function userEpassBookCheck($request,$details,$deviceType)
    {
        //Check device id of current User  and new user
        try{
            $status =  !empty($details) && ($request->device_id != $details->e_passbook_device_id) ? $this->sendNotificationEPassbook($details,$request) : false;
            $details->update(['e_passbook_device_id' => $request->device_id,'e_passbook_mobile_token'=>$request->mobile_token]) ;
            
            return $status;
        }
        catch (\Exception $err)
        {
        }
      

    }


    public function sendNotificationEPassbook($memberDetail,$request)
    {
      
        if(!empty($memberDetail))
        {   

        
           
            // Firebase API Integration for Mobile Number Update Start
                $title = "Already Login";                            
                $body = "This Account already Used in Other Device.";
                if($memberDetail->e_passbook_mobile_token !='')
                {                             
                    $dataApi = array("type" => "logout", 'title' => $title, 'sound' => 'default', 'body' => $body,'id' =>$memberDetail->id);
                    $fields = array('to' => $memberDetail->e_passbook_mobile_token, 'data' => $dataApi);
                    $dataApi = json_encode($fields);
                    //FCM API end-point
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
                    $server_key = 'AAAAMiX2FHk:APA91bEkiW8IBU79vW-tk8KaLct1GiCmRkYc7SwQy7Els_A6lGOSiOe9ODtqeCz99RPm1LpNfINa12xJYluWQ10oSFkWxPYMGNKDWJtkYcb9owj_7EF7rmR3fmYz4QoppAy_qKo-jkso';
                    //header with content_type api key
                    $headers = array(
                        'Content-Type:application/json',
                        'Authorization:key='.$server_key
                    );
                    //CURL request to route notification to FCM connection server (provided by Google)
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $dataApi);
                    $result = curl_exec($ch);
                   
                    curl_close($ch);
                    
                    //print_r($result); 
                }
                return true;
                // Firebase API Integration for Mobile Number Update End
        }
    }
}