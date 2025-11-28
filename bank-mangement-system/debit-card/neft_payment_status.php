<?php
date_default_timezone_set("Asia/Calcutta"); 

//Config UAT


$url_neft_uat  = "https://apibankingonesandbox.icicibank.com/api/v1/composite-status";
$post_neft = [
    "URN" => "1235678",
    "AGGRID" => "CUST0675",
    "CORPID" => "PRACHICIB1",
    "USERID" => "USER3",
    "UNIQUEID" => "abc1234500001wer"
];

// stdClass Object ( [REQID] => 391702 [STATUS] => SUCCESS [UNIQUEID] => abc1234588000 [URN] => 1235678 [UTRNUMBER] => 057392521 [RESPONSE] => SUCCESS )



/*

$url_neft_uat  = "https://apibankingonesandbox.icicibank.com/api/v1/CIBNEFTStatus";
$post_neft = [
    "URN" => "100214578449654123",
    "AGGRID" => "HARI12444543",
    "CORPID" => "PRACHICIB1",
    "USERID" => "USER3",
    "UTRNUMBER" => "455849"
];
*/


//$url_neft_uat  = "https://apibankingonesandbox.icicibank.com/api/Corporate/CIB/v1/RegistrationStatus";

/*
$post_neft = [
    "CORPID" => "PRACHICIB1",
    "AGGRNAME"=>"HARI445445",
    "USERID" => "USER3", 
    "AGGRID" => "HARI12444543",
    "BANKID" => "ICI",
    "URN" => "100214578449654123"
];


$post_neft = [
    "URN" => "100214578449654123",
    "AGGRID" => "HARI12444543",
    "CORPID" => "PRACHICIB1",
    "USERID" => "USER3",
    "UTRNUMBER" => "455849"
];
*/

// $post_neft = [
// "CORPID" => "PRACHICIB1",
// "AGGRNAME"=>"SAMRADDHB",
// "USERID" => "USER3", 
// "AGGRID" => "CUST0675",
// "BANKID" => "ICI",
// "URN" => "1235678"
// ];




$apostData = json_encode($post_neft);
print_r("<<========apostData=========>><br />");
print_r($apostData);
$sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

$fp= fopen("ICICIUATpubliccert.txt","r");
$pub_key_string=fread($fp,4096);


//fclose($fp);
openssl_get_publickey($pub_key_string);
openssl_public_encrypt($sessionKey,$encryptedKey,$pub_key_string); // RSA

$iv = 1234567890123456; //str_repeat("\0", 16);

$encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES

$request = [
    "requestId"=> "req_".time(),
    "encryptedKey"=> base64_encode($encryptedKey),
    "iv"=> base64_encode($iv),
    "encryptedData"=> base64_encode($encryptedData),
    "oaepHashingAlgorithm"=> "NONE",
    "service"=> "",
    "clientInfo"=> "",
    "optionalParam"=> ""
];
print_r("<<========request=========>><br />");
print_r($request);



$apostData = json_encode($request);
print_r("<<========apostData=========>><br />");
print_r($apostData);
$httpUrl = $url_neft_uat;
print_r("<<========httpUrl=========>><br />");
print_r($httpUrl);
$headers = array(
    "cache-control: no-cache",
    "accept: application/json",
    "content-type: application/json",
    "apikey: IsGSsHXA6tjGuNc8nshIQQxYGCIH7ryL",
    "x-priority:0010"
);
print_r("<<========headers=========>><br>");
 print_r($headers);


$acurl = curl_init();
curl_setopt_array($acurl, array(
    CURLOPT_URL => $httpUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 300,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $apostData,
    CURLOPT_HTTPHEADER => $headers,
));
 

$aresponse = curl_exec($acurl);
 print_r("<<========aresponse=========>><br />");
$aerr = curl_error($acurl);
$httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
print_r("<<========httpcode=========>><br />");
print_r($httpcode);
print_r("<<========curlresponse=========>><br />");
print_r($aresponse);

if ($aerr) {
    
    echo "cURL Error #:" . $aerr;
} else {
    
    $fp= fopen("uatprivatekey.pem","r");
    $priv_key=fread($fp,8192);
    fclose($fp);
    $res = openssl_get_privatekey($priv_key, "");
    $data = json_decode($aresponse);
 
    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
    $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
    $newsource = substr($encData, 16);

    $log = "\n\n".'GUID - '."================================================================\n <br>";
    $log .= 'URL - '.$httpUrl."\n\n <br>";
    $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n <br>";
    $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n <br>";
    
    // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

    $output = json_decode($newsource);
    print_r("<<========output=========>><br />");
    print_r($output);
}













// <<========apostData=========>>
// {"tranRefNo":"abc1234588000","amount":"10","senderAcctNo":"000451000301","beneAccNo":"000405002777","beneIFSC":"ICIC0000011","beneName":"MEHRAJ123","narration1":"Test Transfer","crpId":"PRACHICIB1","crpUsr":"USER3","aggrName":"SAMRADDHB","aggrId":"CUST0675","urn":"1235678","txnType":"TPA","WORKFLOW_REQD":"N"}<<========request=========>>
// Array ( [requestId] => req_1638948240 [encryptedKey] => CCZWbMoQ/D1NaSLxo+cao9kwQ0V/Z+BQcEemmDAJKWzcr/uTPBvd4dxZAmO+5eLa7AhQiaAZtpMdoYNS/SUKdHrFfaeAZy9Bpjf7ipoT/GhQBqpNbv5rgL0f1eZa4RDUhMfLUS1UnYFnQPaS0++P1y18cJEqBz0fj2ulqP8m+lzFFeic5oqQruEz2RwFZ6ItMiVA45b8Vl2JIyqFrTrPjOcwRHm2xMv3a+IpJRLcWdNs3s7QMvs+uHkhOQVnamimeZkh5ZnJbRWKCZOVV+TvjMDLFViF9f2pI+4i4y4vXVO5tj6Iw4z5uLGPUG80juPy3zEnaCubsPg4I2xNhOw9J2Z4aIXX1j9CfIhodn8rGkMjET9/PgB2Sy4JMBHAmkYu7RdXwoDz9e+3E3cNgTJ5aaggyBRbUflkfK51z9ittZeebNmZrfLduOI+/yNBpN1mryzSjruArPZlVgRPUJI49yLKSZ5blpCE6vo44FxjjS1FcG4N+QaR8zDl5AfJUSoVC7zQ0mRluk2eu+GLgsDqEEC8wIImk4eQkqIE/B5c/mFTbcFZhQ+QKOMFIHRQIJUCevruR3vatJ1tKz60ln4bQmnoAMZj2xQZ9m8U1kX+63wJ0dStnXRikt+UvgRcpTUfANILpgHSzTy2V5mxK/4xejRmGSzUE958+O9KcXCoi4k= [iv] => MTIzNDU2Nzg5MDEyMzQ1Ng== [encryptedData] => Ey6d5EoMzBPcz8wwrlxlB7T5u8xUaF0mdSlStXc800UhJkaw6FEqzAUqN44qvBc4hBjYHhIuLDyGEM1jwadivw7Yvz/rPudgfnGksgkBOsQTFD7/KGUN7/cm5PCnXZKeWVTnaC7KA0vxrOwlOjAtf+31i9NWlSq7ypjHa0Cqh8F7f8eRS3+CwNvH3KiqkUsECSd8rsR1qjWO2OIzE8WoQYfQQwzYWWUkJ9JY8ZjLZnd7m7jjjky1BqVW376CpViS9roPEqihHKmpJ981xRBNrB49RamLpj18bl4tu0S3JFoELPSdTUq7ubPUhhmBXAiiW5ADRuxfMv7gU6KvlX85pVDqOkgUKiosH78IGZeyjJ98nreigfVFil+7dBmOc9Qcq7N+21s9H23M4WCUE82Y4wgqIoFfqqC4hqmPKwskRxs= [oaepHashingAlgorithm] => NONE [service] => [clientInfo] => [optionalParam] => ) <<========apostData=========>>
// {"requestId":"req_1638948240","encryptedKey":"CCZWbMoQ\/D1NaSLxo+cao9kwQ0V\/Z+BQcEemmDAJKWzcr\/uTPBvd4dxZAmO+5eLa7AhQiaAZtpMdoYNS\/SUKdHrFfaeAZy9Bpjf7ipoT\/GhQBqpNbv5rgL0f1eZa4RDUhMfLUS1UnYFnQPaS0++P1y18cJEqBz0fj2ulqP8m+lzFFeic5oqQruEz2RwFZ6ItMiVA45b8Vl2JIyqFrTrPjOcwRHm2xMv3a+IpJRLcWdNs3s7QMvs+uHkhOQVnamimeZkh5ZnJbRWKCZOVV+TvjMDLFViF9f2pI+4i4y4vXVO5tj6Iw4z5uLGPUG80juPy3zEnaCubsPg4I2xNhOw9J2Z4aIXX1j9CfIhodn8rGkMjET9\/PgB2Sy4JMBHAmkYu7RdXwoDz9e+3E3cNgTJ5aaggyBRbUflkfK51z9ittZeebNmZrfLduOI+\/yNBpN1mryzSjruArPZlVgRPUJI49yLKSZ5blpCE6vo44FxjjS1FcG4N+QaR8zDl5AfJUSoVC7zQ0mRluk2eu+GLgsDqEEC8wIImk4eQkqIE\/B5c\/mFTbcFZhQ+QKOMFIHRQIJUCevruR3vatJ1tKz60ln4bQmnoAMZj2xQZ9m8U1kX+63wJ0dStnXRikt+UvgRcpTUfANILpgHSzTy2V5mxK\/4xejRmGSzUE958+O9KcXCoi4k=","iv":"MTIzNDU2Nzg5MDEyMzQ1Ng==","encryptedData":"Ey6d5EoMzBPcz8wwrlxlB7T5u8xUaF0mdSlStXc800UhJkaw6FEqzAUqN44qvBc4hBjYHhIuLDyGEM1jwadivw7Yvz\/rPudgfnGksgkBOsQTFD7\/KGUN7\/cm5PCnXZKeWVTnaC7KA0vxrOwlOjAtf+31i9NWlSq7ypjHa0Cqh8F7f8eRS3+CwNvH3KiqkUsECSd8rsR1qjWO2OIzE8WoQYfQQwzYWWUkJ9JY8ZjLZnd7m7jjjky1BqVW376CpViS9roPEqihHKmpJ981xRBNrB49RamLpj18bl4tu0S3JFoELPSdTUq7ubPUhhmBXAiiW5ADRuxfMv7gU6KvlX85pVDqOkgUKiosH78IGZeyjJ98nreigfVFil+7dBmOc9Qcq7N+21s9H23M4WCUE82Y4wgqIoFfqqC4hqmPKwskRxs=","oaepHashingAlgorithm":"NONE","service":"","clientInfo":"","optionalParam":""}<<========httpUrl=========>>
// https://apibankingonesandbox.icicibank.com/api/v1/composite-payment<<========headers=========>>
// Array ( [0] => cache-control: no-cache [1] => accept: application/json [2] => content-type: application/json [3] => apikey: IsGSsHXA6tjGuNc8nshIQQxYGCIH7ryL [4] => x-priority:0010 ) <<========httpcode=========>>
// 200<<========curlresponse=========>>
// { "requestId": "req_1638948240", "service": "UPI", "encryptedKey": "iiDmXxVmb/wcw4Y1RdBDHGoeUevoJ7w4hk8oxaXE67UiAYnqj0M+xeqfZmhp37OPXftVmdKtplY1qaPPaldQUxhNdGvTc+Pv4eYBeVrIwoMAgdzkzbn5M6EDLOstKag92EyLMcBZ4lzqLRZCLw2lC8Kh/o3pKNAPDGX35CqVxdJ3znP2NP5NQ/Nt8mpHWHzbVsEGmUXd1Gr93SYWRhW92TZnyW/Dqu37ioxAZMrKOUthyHz8Bv6k6YVYyB4s+10oPv6IyNA4WXpqDMUQh+YYkXu8Luu5M03ktCvFowWJ1jZm3If01phJhngPPvjRNAe4EyMB4g865EoWSa0eALBzh2jgQWXjNValW+9ZIKmdkEZzD7ahCzV3dRlQPpHRrfow4lEfMKLsDrg9bldqcComqz8+KhqvxWZz5c0/oP2b+1S6r4eUprjkQ3zIHKmg312DBdgBUnhxfleMgAnfZiAGXF7wIvMKjpSAduyxLkuqOQUhFrc/zT6hVasrApi50aXrZ9WLut77ke4iWwreVx7Nj3a4cKbAG6CoM8e3gx25zGwDncFcOs+i93KIutfuVbB29YznlgRaZXBUK1k/VFA496/gb5Z91AuWFrmhLJsSBEYO6nJqbEPCRrdNNPBscGv5usqTXz9eeDcgaeJfecUG5VNRm95GsJxr3fSBSxtbiTc=", "oaepHashingAlgorithm": "NONE", "iv": "", "encryptedData": "IwVKwI+GOI5rVQNXRdNUAzOaY6DEda47VUBIIUJV1QQ/tAxob3hSBp0KoZ7WjPGQP84iDcVbxZl5mPpEIwA0+ttgu4WhNvqh63ySfa3QgmchMlIpbqCFCIOa3xFItAfKWJvW+bBqEkGwQzOSQw973A==", "clientInfo": "", "optionalParam": "" } <<========output=========>>
// stdClass Object ( [errorCode] => 997 [description] => Note : Initaite Status check after Some time )

?>