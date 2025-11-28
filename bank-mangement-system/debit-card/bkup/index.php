<?php
// ini_set('display_startup_errors', 1);
// ini_set('display_errors', 1);
// error_reporting(-1);
date_default_timezone_set("Asia/Calcutta"); 

//Config UAT
$url  = "https://apibankingonesandbox.icicibank.com/api/v1/composite-status";
$url_cib_reg = "https://apibankingonesandbox.icicibank.com/api/Corporate/CIB/v1/Registration";


$post_cib_reg = [
    "CORPID" => "PRACHICIB1",
    "AGGRNAME"=>"SAMRADDHB",
    "USERID" => "USER3", 
    "AGGRID" => "CUST0675",
    "BANKID" => "ICI",
    "URN" => "1235678"
];



$apostData = json_encode($post_cib_reg);
// print_r("<<========apostData=========>><br />");
// print_r($apostData);
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
// print_r("<<========request=========>><br />");
// print_r($request);



$apostData = json_encode($request);
// print_r("<<========apostData=========>><br />");
// print_r($apostData);
$httpUrl = $url_cib_reg;
// print_r("<<========httpUrl=========>><br />");
// print_r($httpUrl);
$headers = array(
    "cache-control: no-cache",
    "accept: application/json",
    "content-type: application/json",
    "apikey: y8GDQi6mlxgyKzzMGbpiAyPiOoDe385V",
    "x-priority:0010"
);
// print_r("<<========headers=========>><br>");
//  print_r($headers);


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















// //$url  = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment/status/neft-rtgs";
// $debitcardaccount = "000451000301";
// $ifsc = "ICIC0000011";
// $txnType = "TPA";
// $xpriority = "0010";

// // $post = array(
// //     "tranRefNo" => "abc123456",
// //     "amount" =>  "10.00",
// //     "senderAcctNo" => "000451000301",
// //     "device-id" => "400438400438400438400438",
// //     "mobile" => "7988000014",
// //     "channel-code" => "MICICI",
// //     "profile-id" => "2996304",
// //     "beneAccNo" => "000405002777",
// //     "beneName" => "Mehul",
// //     "beneIFSC" => "ICIC0000011",
// //     "narration1" => "Test",
// //     "crpId" => "PRACHICIB1",
// //     "crpUsr" => "ABC",
// //     "aggrId" => "AGGR0028",
// //     "urn" => "759969775cff4dcc8b8c63402e645da3", 
// //     "aggrName" => "Abc",
// //     "txnType" => "TPA",
// //     "WORKFLOW_REQD" => "N"
// // );

// $post_cib_reg = array(
//         "CORPID" => "PRACHICIB1",
//         "AGGRNAME"=>"SAMRADDHB",
//         "USERID" => "123912391", 
//         "AGGRID" => "CUST0675",
//         "BANKID" => "ICI",
//         "URN" => "3kCy4sPuSqDNi4kggXJIoE568221"
// );


// $data = cid_curl(json_encode($post_cib_reg), $url_cib_reg, $method="POST", $json=true, $ssl=true);

// print_r($data);
// die;

// function cid_curl($post_cib_reg = array(), $url_cib_reg, $method = "POST", $json = true, $ssl = true){
//     $ch = curl_init();  
//     curl_setopt($ch, CURLOPT_URL, $url_cib_reg);    
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     if($method == 'POST'){
//         curl_setopt($ch, CURLOPT_POST, 1);
//     }
//     if($json == true){
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post_cib_reg);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//             'Content-Type: application/json','Content-Length: ' . strlen($post_cib_reg), 'apikey:  y8GDQi6mlxgyKzzMGbpiAyPiOoDe385V'));
//     }else{
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_cib_reg));
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//     }
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSLVERSION, 6);
//     if($ssl == false){
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     }
//     // curl_setopt($ch, CURLOPT_HEADER, 0);     
//     $r = curl_exec($ch);    
    
//     if (curl_error($ch)) {
//         $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         $err = curl_error($ch);
//         print_r('Error: ' . $err . ' Status: ' . $statusCode);
//         // Add error
//         $this->error = $err;
//     }
//     curl_close($ch);
//     return $r;
// }


// function curl($post = array(), $url, $token = '', $method = "POST", $json = true, $ssl = true, $xpriority="0010"){
//     $ch = curl_init();  
//     curl_setopt($ch, CURLOPT_URL, $url);    
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
//     if($method == 'POST'){
//         curl_setopt($ch, CURLOPT_POST, 1);
//     }
//     if($json == true){
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//             'Content-Type: application/json','Content-Length: ' . strlen($post), 'apikey:  IsGSsHXA6tjGuNc8nshIQQxYGCIH7ryL', 'x-priority: '.$xpriority));
//     }else{
//         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
//         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
//     }
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_SSLVERSION, 6);
//     if($ssl == false){
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//     }
//     // curl_setopt($ch, CURLOPT_HEADER, 0);     
//     $r = curl_exec($ch);    
    
//     if (curl_error($ch)) {
//         $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         $err = curl_error($ch);
//         print_r('Error: ' . $err . ' Status: ' . $statusCode);
//         // Add error
//         $this->error = $err;
//     }
//     curl_close($ch);
//     return $r;
// }




//UPI
// $post = array(
//     "device-id"=> "XXXXXXXXXXXXXXXXXX",
//     "mobile"=> "XXXXXXXXXXX",
//     "channel-code"=> "MICICI",
//     "profile-id"=> "XXXXXXX",
//     "seq-no"=> "ICI1f8qsftrryrtt4tstir01",
//     "account-provider"=> "74",
//     "use-default-acc"=> "D",
//    "payee-va"=>"hen@icici",
//     "payer-va"=> "XXXXXXXXXXX",
//     "amount"=> "3.50",
//     "pre-approved"=> "P",
//     "default-debit"=> "N",
//     "default-credit"=> "N",
//     "txn-type"=> "merchantToPersonPay",
//     "remarks"=> "none"
//     "mcc"> "6011",
//     "merchant-type"=> "ENTITY",
// );


//config LIVE
//$url = "https://apibankingone.icicibank.com/api/v1/composite-payment";
?>