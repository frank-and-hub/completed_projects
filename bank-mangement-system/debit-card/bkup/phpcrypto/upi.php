<?php

date_default_timezone_set("Asia/Calcutta"); 
$reques_params = [
   "device-id"=> "410706410706410706410706",
    "mobile"=> "9840239706",
    "channel-code"=> "MICICI",
    "profile-id"=> "63377637",
    "seq-no"=> date('YmdHis'),
    "account-provider"=> "74",
    "use-default-acc"=> "D",
    "payee-va"=>"9990062799@ybl",
    "payer-va"=> "PAYMYCHOICE@icici",
    "amount"=> "1",
    "pre-approved"=> "P",
    "default-debit"=> "N",
    "default-credit"=> "N",
    "txn-type"=> "merchantToPersonPay",
    "remarks"=> "none",
    "mcc"=> "6012",
    "merchant-type"=> "ENTITY",
];
$apostData = json_encode($reques_params);
print_r("<<========apostData=========>>");
print_r($apostData);
$sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

$fp= fopen("prod_public.txt","r");
$pub_key_string=fread($fp,8192);
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

print_r("<<========request=========>>");
print_r($request);
// echo "Time: ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Session key: ".$sessionKey.PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Base64 Session key: ".base64_encode($sessionKey).PHP_EOL.PHP_EOL; echo "<br/>";
// echo "Decrypted Request: ".$apostData.PHP_EOL.PHP_EOL; echo "<br/>";
// echo "encryptedKey: ".$request['encryptedKey'].PHP_EOL.PHP_EOL; echo "<br/>";
// echo "encryptedData: ".$request['encryptedData'].PHP_EOL.PHP_EOL; echo "<br/>";
// echo "iv: ".$request['iv'].PHP_EOL.PHP_EOL; echo "<br/>";

$apostData = json_encode($request);
print_r("<<========apostData=========>>");
print_r($apostData);
$httpUrl = "https://apibankingone.icicibank.com/api/v1/composite-payment";
print_r("<<========httpUrl=========>>");
print_r($httpUrl);
$headers = array(
    "cache-control: no-cache",
    "accept: application/json",
    "content-type: application/json",
    "apikey: rllvZbpJDQ6CaJ21bJrUhBnICM9xM5Tt",
    "x-priority:1000"
);
print_r("<<========headers=========>>");
print_r($headers);
// $file = 'logFiles.txt';

// $log = "\n\n".'GUID - '.time()."================================================================\n";
// $log .= 'URL - '.$httpUrl."\n\n";
// $log .= 'HEADER - '.json_encode($headers)."\n\n";
// $log .= 'REQUEST - '.json_encode($reques_params)."\n\n";
// $log .= 'REQUEST ENCRYPTED - '.$apostData."\n\n";

// file_put_contents($file, $log, FILE_APPEND | LOCK_EX);



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
print_r("<<========aresponse=========>>");
print_r($aresponse);
$aerr = curl_error($acurl);
$httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
print_r("<<========httpcode=========>>");
print_r($httpcode);

if ($aerr) {
    
    echo "cURL Error #:" . $aerr;
} else {
    
    $fp= fopen("prod_private.pem","r");
    $priv_key=fread($fp,8192);
    fclose($fp);
    $res = openssl_get_privatekey($priv_key, "");
    $data = json_decode($aresponse);
    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
    $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
    $newsource = substr($encData, 16);

    // $log = "\n\n".'GUID - '."================================================================\n";
    // $log .= 'URL - '.$httpUrl."\n\n";
    // $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n";
    // $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n";
    
    // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

    $output = json_decode($newsource);
    print_r("<<========output=========>>");
print_r($output);
}
