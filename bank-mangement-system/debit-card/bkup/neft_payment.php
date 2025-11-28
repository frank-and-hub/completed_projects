<?php
date_default_timezone_set("Asia/Calcutta"); 

//Config UAT
$url_neft_uat  = "https://apibankingonesandbox.icicibank.com/api/v1/composite-payment";



$post_neft = [
    "tranRefNo" => "sss1",
    "amount" => "10",
    "senderAcctNo" => "000451000301",
    "beneAccNo" => "000405002777",
    "beneIFSC" => "ICIC0000011",
    "beneName" => "PRAVESH",
    "narration1" => "1235678",
    "crpId" => "PRACHICIB1",
    "crpUsr" => "USER3",
    "aggrName" => "SAMRADDHB",
    "aggrId" => "CUST0675",
    "urn" => "1235678",
    "txnType" => "TPA",
    "WORKFLOW_REQD" => "N",
    "CREDITACC"=> "4629525412777954",

];


$apostData = json_encode($post_neft);
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
$httpUrl = $url_neft_uat;
// print_r("<<========httpUrl=========>><br />");
// print_r($httpUrl);
$headers = array(
    "cache-control: no-cache",
    "accept: application/json",
    "content-type: application/json",
    "apikey: IsGSsHXA6tjGuNc8nshIQQxYGCIH7ryL",
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
// print_r("<<========aresponse=========>><br />");
$aerr = curl_error($acurl);
$httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
// print_r("<<========httpcode=========>><br />");
// print_r($httpcode);
// print_r("<<========curlresponse=========>><br />");
// print_r($aresponse);

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


?>