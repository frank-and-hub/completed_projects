<?php

/*
Following notes are from my research regarding padding (text not mine).
Read to understand the confusion between PKCS7 (default padding) and 
PKCS5 (which we are trying to achieve here) :
OPENSSL_ZERO_PADDING has a direct impact on the OpenSSL context.  EVP_CIPHER_CTX_set_padding() enables or disables padding (enabled by default).  So, OPENSSL_ZERO_PADDING disables padding for the context, which means that you will have to manually apply your own padding out to the block size.  Without using OPENSSL_ZERO_PADDING, you will automatically get PKCS#7 padding.
OPENSSL_RAW_DATA does not affect the OpenSSL context but has an impact on the format of the data returned to the caller.  When OPENSSL_RAW_DATA is specified, the returned data is returned as-is.  When it is not specified, Base64 encoded data is returned to the caller.
*/

$data  = "SAMMRADDHBESTWIN"; 
$hashedData = hash("sha256", $data, true);
$secretKey = "3f4dbbdad1a241bca994339c0d3f3efd";
$ivStr = "SAMMRADDHBESTWIN";
$key = pack('H*', $secretKey);
$iv = pack('H*', $ivStr);
$inputData = pkcs5_pad($hashedData, 16);
showB64('key', $key);
showB64('iv', $iv);
showB64('hashedData', $hashedData);
showB64('inputData', $inputData);
$checksum = encrypt($key, $inputData, $iv);
showB64('checksum', $checksum);

function encrypt($key,$data,$iv){
  $cipher = 'AES-256-CBC';
  $options = OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING;
  $raw = openssl_encrypt(
  $data,
  $cipher,
  $key,
  $options,
  $iv
  );
  return $raw;
}

function pkcs5_pad($text, $blocksize){ 
  $pad = $blocksize - (strlen($text) % $blocksize); 
  return $text . str_repeat(chr($pad), $pad); 
}     
  
function showB64($label, $rawData) {
  echo "{$label} :".base64_encode($rawData)."\n<br>";
}

/* Sample output :
key :AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=
iv :AAAAAAAAAAAAAAAAAAAAAA==
hashedData :ZAgNCUfIbdT9EjdkCb3XDNpMFGV34rXNjcTOQ9cdZ3w=
inputData :ZAgNCUfIbdT9EjdkCb3XDNpMFGV34rXNjcTOQ9cdZ3wQEBAQEBAQEBAQEBAQEBAQ
checksum :9NS/ZKMscpa4V7i2YQQPoycxCwbL1BlK3h9O/1ujoD1iYgjE8tZx+JRGflw5WikH
*/