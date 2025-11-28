<?php

namespace App\Traits;

use App\Models\PurchaseLog;
use App\Models\SubscribedOrgTransaction;
use App\Models\SubscriptionPackages;
use App\Models\SubscriptionWebhook;
use App\Models\UserSubscription;
use Google_Client;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Google_Service_AndroidPublisher;
use Carbon\Carbon;
use Google\Client;
use Illuminate\Support\Facades\Log;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

trait Common_trait
{
    public function create_unique_slug($string = '', $table = '', $field = 'slug', $col_name = null, $old_slug = null)
    {
        $slug = Str::of($string)->slug('-');
        $slug = strtolower($slug);

        $i = 0;
        $params = array();
        $params[$field] = $slug;
        if ($col_name) {
            $params["$col_name"] = "<> $old_slug";
        }

        while (DB::table($table)->where($params)->count()) {
            if (!preg_match('/-{1}[0-9]+$/', $slug)) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace('/[0-9]+$/', ++$i, $slug);
            }
            $params[$field] = $slug;
        }
        return $slug;
    }

    // public function file_upload($file, $path, $fileName = null)
    // {
    //     $disk = config('constants.file_upload_location');

    //     // $path = Storage::disk(config('constants.file_upload_location'))->put($path, $file);

    //     if (!$fileName) {
    //         $fileName = time() . '-' . $file->getClientOriginalName();
    //     }

    //     $path = $file->storeAs($path, $fileName, $disk);
    //     return $path;
    // }

    public function deleteFile($filePath)
    {
        if ($filePath) {
            $disk = config('constants.file_upload_location');

            if (Storage::disk($disk)->exists($filePath)) {
                Storage::disk($disk)->delete($filePath);
                return true;
            }
        }

        return false;
    }

    // public function sendOTP($to = '', $data = [], $message = '')
    // {
    //     $msg = $this->replacePlaceholders($data, $message);

    //     //return true;

    //     $postData = [
    //         'To' => $to,
    //         'From' => '+' . env('TWILIO_FROM_NUMBER'),
    //         'Body' => $msg,
    //     ];

    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => 'https://api.twilio.com/2010-04-01/Accounts/' . env('TWILIO_ACCOUNT_SID') . '/Messages.json',
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => 'POST',
    //         CURLOPT_POSTFIELDS => http_build_query($postData),
    //         CURLOPT_HTTPHEADER => array(
    //             'Content-Type: application/x-www-form-urlencoded',
    //             'Authorization: Basic ' . base64_encode(env('TWILIO_ACCOUNT_SID') . ':' . env('TWILIO_AUTH_TOKEN'))
    //         ),
    //         CURLOPT_SSL_VERIFYHOST => 0,
    //         CURLOPT_SSL_VERIFYPEER => 0,
    //     ));

    //     $response = curl_exec($curl);
    //     $error = curl_error($curl);

    //     curl_close($curl);

    //     if ($error) {
    //         Log::error("Twilio OTP sending failed", [
    //             'to' => $to,
    //             'error' => $error
    //         ]);
    //         return false;
    //     } else {
    //         Log::info("Twilio OTP sent successfully", [
    //             'to' => $to,
    //             'response' => $response
    //         ]);
    //         return true;
    //     }
    // }

    public function sendEmail($email = '', Mailable $mailable): bool
    {
        try {
            Mail::to($email)->send($mailable);

            Log::info("OTP email sent successfully to: " . $email);
            return true;
        } catch (\Exception $e) {
            // Log failure
            Log::error("Failed to send OTP email to: " . $email . ". Error: " . $e->getMessage());

            return false;
        }
    }

    function replacePlaceholders($replacements,  $message): string
    {
        $hasPlaceholder = false;

        foreach ($replacements as $key => $value) {
            if (strpos($message, "##$key##") !== false) {
                $hasPlaceholder = true;
                $message = str_replace("##$key##", $value, $message);
            }
        }
        return $hasPlaceholder ? $message : $message;
    }

    public function file_upload($file, $folder)
    {
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/' . $folder;
        $file->move(public_path($path), $fileName);
        return $path . '/' . $fileName;
    }

    public function applySearch($query, $search, array $columns = ['name'])
    {
        if ($search) {
            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', '%' . $search . '%');
                }
            });
        }

        return $query;
    }

    // public function verifyAndroidreceipt($data = [])
    // {
    //     $user = Auth::user();

    //     $purchaseLog = PurchaseLog::where('user_id', $user->id)
    //         ->orderByDesc('created_at')
    //         ->first();

    //     if (!$purchaseLog) {
    //         return response()->json(['error' => 'No purchase log found.'], 404);
    //     }

    //     $receipt = json_decode($purchaseLog->purchaseReceipt, true);

    //     $data = [
    //         'packageName'   => 'com.fitpro360',
    //         'productId'     => $receipt['productId'] ?? null,
    //         'purchaseToken' => $receipt['purchaseReceipt'] ?? null,
    //         'transactionId' => $receipt['transactionId'] ?? null,
    //         // 'purchaseToken' => $receipt['purchaseToken'] ?? $receipt['transactionId'] ?? null,

    //     ];

    //     if (!isset($data['transactionId']) || empty($data['transactionId'])) {
    //         echo 'The transactionId field is required.';
    //         return;
    //     }

    //     if (!isset($data['purchaseReceipt']) || empty($data['purchaseReceipt'])) {
    //         echo 'The purchaseReceipt field is required.';
    //         return;
    //     }

    //     $transactionId = $data['transactionId'] ?? null;

    //     try {
    //         $client = new \Google_Client();
    //         $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
    //         $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

    //         $service = new \Google_Service_AndroidPublisher($client);

    //         $subscription = $service->purchases_subscriptions->get(
    //             $data['packageName'],
    //             $data['productId'],
    //             $data['purchaseToken'],
    //         );

    //         pree($subscription);

    //         echo 'Subscription still active or not cancelled';
    //     } catch (\Exception $e) {
    //         echo "Error verifying cancellation: ";
    //     }
    // }

    // public function verifyIosreceipt($data = [])
    // {
    //     $jwt = self::generateAppStoreJWT();

    //     if (!isset($data['transactionId']) || empty($data['transactionId'])) {
    //         echo 'The transactionId field is required.';
    //         return;
    //     }

    //     if (!isset($data['purchaseReceipt']) || empty($data['purchaseReceipt'])) {
    //         echo 'The purchaseReceipt field is required.';
    //         return;
    //     }

    //     if (!$jwt) {
    //         echo 'The purchaseReceipt field is required.';
    //         return;
    //     }

    //     $transactionId = $data['transactionId'] ?? null;

    //     $user = Auth::user();

    //     $endpoints = [
    //         'production' => "https://api.storekit.itunes.apple.com/inApps/v1/transactions/{$transactionId}",
    //         // 'sandbox'    => "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/transactions/{$transactionId}",

    //         'sandbox'    => "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/subscriptions/2000000944696388",

    //     ];
    //     $url = $endpoints['sandbox'];

    //     $ch = curl_init($url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //         'Authorization: Bearer ' . $jwt,
    //         'Content-Type: application/json'
    //     ]);

    //     $response = curl_exec($ch);
    //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);

    //     if ($httpCode !== 200 || !$response) {
    //         return $this->sendError('Failed to fetch transaction from Apple servers', null, 400);
    //     }
    //     $response = json_decode($response, true);
    //     pree($response);

    //     if (isset($response['signedTransactionInfo'])) {
    //         $transactionPayload = self::decodeSignedTransactionInfo($response['signedTransactionInfo']);

    //         pree($transactionPayload);

    //         if ($transactionPayload) {
    //             $productId = $transactionPayload['productId'] ?? '';
    //             $purchaseDate = $transactionPayload['purchaseDate'] ?? '';
    //             $expiresDate = $transactionPayload['expiresDate'] ?? '';
    //             $transactionId = $transactionPayload['transactionId'] ?? '';

    //             $validationResult = $transactionPayload;

    //             $purchaseLog = new PurchaseLog();
    //             $purchaseLog->user_id = $user->id;
    //             $purchaseLog->purchaseReceipt = json_encode($data);
    //             $purchaseLog->transaction_id = $data['transactionId'];


    //             if ($validationResult) {
    //                 $purchaseLog->verification_data = json_encode($validationResult);
    //             }
    //             $purchaseLog->save();
    //         } else {
    //             echo 'Invalid signed transaction payload';
    //         }
    //     } else {
    //         echo 'Invalid signed transaction payload';
    //     }
    // }


    public function getDataFromWebhookIos($webhookData, $originalTransactionId, $user_id)
    {
        $webhookData = SubscriptionWebhook::find(11);

        $webhookArray = json_decode($webhookData->data);
        if (!isset($webhookArray->signedPayload)) {
            Log::error('signedPayload property not found in webhook data', ['webhookArray' => $webhookArray]);
            return null; // or handle the error as needed
        }
        $jwtDecode = self::decodeSignedTransactionInfo($webhookArray->signedPayload);

        // Check if notificationType exists and is "subscribed"
        if (isset($jwtDecode['notificationType']) && strtolower($jwtDecode['notificationType']) === 'subscribed') {

            if (isset($jwtDecode['data']['status'])) {
                $status = $jwtDecode['data']['status'];

                if ($status == 1) {
                    // Handle active subscription
                    $signedTransactionInfoJWT = $jwtDecode['data']['signedTransactionInfo'];
                    // Decode JWT (no signature validation here, for Apple JWT use their public key if needed)
                    $signedPayloadJwtDecode = self::decodeSignedTransactionInfo($signedTransactionInfoJWT);


                    $originalTransactionId = $signedPayloadJwtDecode['originalTransactionId'] ?? null;

                    if ($originalTransactionId) {

                        $webhookData->originalTransactionId = $originalTransactionId;
                        $webhookData->save();

                        // $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first(); // transactionId = org transaction id

                        $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();


                        $webhookData->status = 2;
                        $webhookData->save();

                        $userSubscription = new UserSubscription();
                        $userSubscription->user_id = $user_id;
                        $userSubscription->transaction_id = $signedPayloadJwtDecode['transactionId'];
                        $userSubscription->originalTransactionId = $originalTransactionId;
                        $userSubscription->subscription_id = $subscriptionPackagesData->id;
                        $userSubscription->payment_gateway = 'ios';
                        $userSubscription->subscribed_at =  (int) ($signedPayloadJwtDecode['purchaseDate'] / 1000);
                        $userSubscription->expires_at = (int) ($signedPayloadJwtDecode['expiresDate'] / 1000);
                        $userSubscription->is_recurring = 1;
                        $userSubscription->amount = $signedPayloadJwtDecode['price'];
                        $userSubscription->currency = $signedPayloadJwtDecode['currency'];
                        $userSubscription->status = 'active';

                        $userSubscription->save();


                        return $userSubscription;
                    }
                }
            }
        }
    }

    // public function getAndroidSubscriptionDetails($packageName, $subscriptionId, $purchaseToken)
    // {

    //     // $client = new Google_Client();
    //     // $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
    //     // $client->addScope(Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
    //     // $service = new Google_Service_AndroidPublisher($client);

    //     // Path to your service account file
    //     // $keyFilePath = storage_path('app/google/service-account.json');
    //     $keyFilePath = base_path(env('GOOGLE_PLAY_JSON_KEY_PATH'));

    //     $client = new Client();
    //     $client->setAuthConfig($keyFilePath);
    //     $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

    //     $accessToken = $client->fetchAccessTokenWithAssertion();

    //     if (isset($accessToken['error'])) {
    //         throw new \Exception("Google Auth Error: " . $accessToken['error_description']);
    //     }

    //     $token = $accessToken['access_token'];

    //     // Build the request URL
    //     // $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$subscriptionId}/tokens/{$purchaseToken}";

    //     // // Make the GET request
    //     // $client = new \GuzzleHttp\Client();
    //     // $response = $client->get($url, [
    //     //     'headers' => [
    //     //         'Authorization' => 'Bearer ' . $token,
    //     //     ]
    //     // ]);

    //     $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptions/{$subscriptionId}/tokens/{$purchaseToken}";
    //     // $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptionsv2/tokens/{$purchaseToken}";
    //     $ch = curl_init();

    //     curl_setopt_array($ch, [
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER => [
    //             'Authorization: Bearer ' . $token,
    //             'Accept: application/json',
    //         ],
    //     ]);

    //     $response = curl_exec($ch);
    //     curl_close($ch);
    //     Log::info('Google Play Raw Subscription Response:', ['response' => $response]);

    //     // $webhookData = new SubscriptionWebhook();
    //     // $webhookData->originalTransactionId = '666666666';
    //     // $webhookData->data = json_encode($response);
    //     // $webhookData->type = '666666666';
    //     // $webhookData->created_at = now();
    //     // $webhookData->status = 2;
    //     // $webhookData->save();

    //     // Decode and return JSON response
    //     return json_decode($response, true);

    //     // $client = new \GuzzleHttp\Client();
    //     // $response = $client->get($url, [
    //     //     'headers' => [
    //     //         'Authorization' => 'Bearer ' . $token,
    //     //     ]
    //     // ]);

    //     // // Decode and return JSON response
    //     // return json_decode($response->getBody(), true);
    // }
    function getSubscriptionDetailsv2($packageName, $purchaseToken)
    {
        try {
            $keyFilePath = base_path(env('GOOGLE_PLAY_JSON_KEY_PATH'));

            $client = new \Google_Client();
            $client->setAuthConfig($keyFilePath);
            $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
            $accessToken = $client->fetchAccessTokenWithAssertion();

            if (isset($accessToken['error'])) {
                throw new \Exception("Google Auth Error: " . $accessToken['error_description']);
            }

            $token = $accessToken['access_token'];

            $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/{$packageName}/purchases/subscriptionsv2/tokens/{$purchaseToken}";
            // pree($url);
            $http = new \GuzzleHttp\Client();
//  pree($http);
            $response = $http->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
            ]);
// pree($response);
            $result = json_decode($response->getBody(), true);
// pree($result);
            return $result;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error("Network error (DNS/timeout): " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Google API connection failed. Please try again later.',
            ], 503); // Service Unavailable

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error("Google API error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Invalid request to Google API',
            ], 400); // Bad request

        } catch (\Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred while contacting Google API.',
            ], 500); // Internal server error
        }
    }
}
