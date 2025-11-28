<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once "vendor/autoload.php";


use PHPCrypto\Hybrid;
use PHPCrypto\Symmetric;
use PHPCrypto\PublicKey;

$plaintext = 'SAMMRADDHBESTWIN';
$key = '3f4dbbdad1a241bca994339c0d3f3efd'; // This can be also a user's password we generate a new
                       // one for encryption using PBKDF2 algorithm

$cipher = new Hybrid(); // AES + HMAC-SHA256 by default
$ciphertext = $cipher->encrypt($plaintext, $key);

// or passing the $key as optional paramter
// $ciphertext = $cipher->encrypt($plaintext, $key);

$result = $cipher->decrypt($ciphertext);

// or passing the $key as optional paramter
// $result = $cipher->decrypt($ciphertext, $key);

print ($result === $plaintext) ? "OK" : "FAILURE";