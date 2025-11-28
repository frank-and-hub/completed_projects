<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Models\PurchaseLog;
use App\Models\SubscribedOrgTransaction;
use App\Models\SubscriptionPackages;
use App\Models\SubscriptionWebhook;
use App\Models\TransactionDetails;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSubscription;
use App\Traits\Common_trait;
use Google_Client;
use Google_Service_AndroidPublisher;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// mail
use App\Models\User;
use App\Models\UserSubscription as UserSubscriptionModel;
use App\Mail\SubscriptionExpireMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;


class SubscriptionController extends BaseApiController
{
    use Common_trait;
    protected $androidPublisherService;

    public function __construct()
    {
        // Initialize Google AndroidPublisher service client
        $client = new \Google_Client();
        $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
        $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
        $this->androidPublisherService = new \Google_Service_AndroidPublisher($client);
    }
    /**
     * Save purchase log and create a new subscription for the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    // public static function ValidateIosSubscriptionReceipt($transactionId, $mode = 'sandbox')
    // {
    //     if (empty($transactionId)) {
    //         return false;
    //     }

    //     // Apple endpoints for StoreKit 2
    //     $endpoints = [
    //         'production' => 'https://api.storekit.itunes.apple.com/inApps/v1/transactions/{transactionId}',
    //         'sandbox'    => 'https://api.storekit-sandbox.itunes.apple.com/inApps/v1/transactions/2000000943723899'
    //     ];

    //     $url = $endpoints[$mode] . $transactionId;

    //     // JWT for authorization
    //     $jwt = self::generateAppStoreJWT();

    //     if (!$jwt) {
    //         return false;
    //     }
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
    //         return false;
    //     }

    //     $decoded = json_decode($response, true);

    //     return $decoded ?: false;
    // }
    protected static function generateAppStoreJWT()
    {
        $privateKeyPath = env('APPSTORE_PRIVATE_KEY_PATH');
        $keyId          = env('APPSTORE_KEY_ID');
        $issuerId       = env('APPSTORE_ISSUER_ID');

        if (!file_exists($privateKeyPath)) {

            return null;
        }

        $privateKey = file_get_contents($privateKeyPath);
        $now = time();
        $token = [
            'iss' => $issuerId,
            'iat' => $now,
            'exp' => $now + 1800,
            'aud' => 'appstoreconnect-v1',
            'bid' =>  env('PACKAGE_NAME') ?? 'com.fitpro360',
        ];

        $headers = [
            'alg' => 'ES256',
            'kid' => '9VC79U759S',
            'typ' => 'JWT'
        ];

        return \Firebase\JWT\JWT::encode(
            $token,
            $privateKey,
            'ES256',
            $keyId,
            $headers
        );
    }
    /**
     * Decode the signed transaction info from the JWT.
     *
     * @param string $signedTransaction
     * @return array|false
     */
    public static function decodeSignedTransactionInfo($signedTransaction)
    {


        $parts = explode('.', $signedTransaction);
        if (count($parts) !== 3) {
            return false;
        }

        $header = json_decode(base64_decode(strtr($parts[0], '-_', '+/')), true);
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        return $payload;
    }

    // public function olddsavePurchaseLog(Request $request)
    // {

    //     $endpoints = [
    //         'production' => 'https://api.storekit.itunes.apple.com/inApps/v1/transactions/{transactionId}',
    //         'sandbox'    => 'https://api.storekit-sandbox.itunes.apple.com/inApps/v1/transactions/2000000943722374'
    //     ];
    //     $url = $endpoints['sandbox'];

    //     // JWT for authorization
    //     $jwt = self::generateAppStoreJWT();

    //     if (!$jwt) {
    //         return false;
    //     }
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
    //         return false;
    //     }
    //     $response = $decoded = json_decode($response, true);

    //     if (isset($response['signedTransactionInfo'])) {
    //         $transactionPayload = self::decodeSignedTransactionInfo($response['signedTransactionInfo']);
    //         if ($transactionPayload) {
    //             $productId = $transactionPayload['productId'] ?? '';
    //             $purchaseDate = $transactionPayload['purchaseDate'] ?? '';
    //             $expiresDate = $transactionPayload['expiresDate'] ?? '';
    //             $transactionId = $transactionPayload['transactionId'] ?? '';

    //             // Use/save this info as needed
    //             Log::info("iOS Subscription verified for product $productId, expires at $expiresDate");
    //         }
    //     }
    //     return $decoded ?: false;

    //     $user = Auth::user();
    //     if (!$user) {
    //         return $this->sendError('Unauthorized', 401);
    //     }

    //     $requiredKeys = ['purchaseReceipt', 'secretKey', 'subscriptionType', 'deviceType', 'productId', 'price'];
    //     foreach ($requiredKeys as $key) {
    //         if (!$request->has($key)) {
    //             return $this->sendError("Missing required field: $key", null, 422);
    //         }
    //     }
    //     $validationResult = null;
    //     if ($request->deviceType === 'android') {
    //         if (!$request->has(['productId', 'secretKey'])) {
    //             return $this->sendError("Missing required fields for Android verification", null, 422);
    //         }

    //         $receiptData = [
    //             'packageName' => 'com.fitpro360',
    //             'productId' => $request->productId,
    //             'purchaseToken' => $request->purchaseReceipt
    //         ];


    //         $validationResult = $this->ValidateAndroidSubscriptionReceipt($receiptData);

    //         if (!$validationResult) {
    //             return $this->sendError('Invalid Android purchase receipt', null, 400);
    //         }

    //         if ($validationResult->getPaymentState() !== 1) {
    //             return $this->sendError('Android subscription payment not completed', null, 400);
    //         }
    //     } else if ($request->deviceType === 'ios') {

    //         $receiptData = $request->purchaseReceipt; // must be the base64 string only
    //         $validationResult = $this->ValidateIosSubscriptionReceipt($receiptData);
    //         if (!$validationResult) {
    //             return $this->sendError('Invalid iOS purchase receipt', null, 400);
    //         }

    //         if (!isset($validationResult['status']) || $validationResult['status'] !== 0) {
    //             return $this->sendError('iOS subscription not valid', null, 400);
    //         }
    //     } else {
    //         return $this->sendError('Invalid device type', null, 400);
    //     }

    //     $purchaseLog = new PurchaseLog();
    //     $purchaseLog->user_id = $user->id;
    //     $purchaseLog->purchaseReceipt = json_encode($request->all());
    //     $purchaseLog->transaction_id = $request->purchaseReceipt;

    //     if ($validationResult) {
    //         $purchaseLog->verification_data = json_encode($validationResult);
    //     }
    //     $purchaseLog->save();

    //     $existingSubscription = UserSubscription::where('user_id', $user->id)
    //         ->where('status', 'active')
    //         ->whereNull('deleted_at')
    //         ->orderByDesc('subscribed_at')
    //         ->first();

    //     if ($existingSubscription) {
    //         if ($existingSubscription->subscription_id == $request->subscriptionType) {
    //             return $this->sendError('This plan already exists for the user.', null, 409);
    //         } else {
    //             $existingSubscription->is_recurring = 0;
    //             $existingSubscription->expires_at = now();
    //             $existingSubscription->status = 'expired';
    //             $existingSubscription->save();
    //         }
    //     }

    //     $userSubscription = new UserSubscription();
    //     $userSubscription->user_id = $user->id;
    //     $userSubscription->subscription_id = $request->subscriptionType;
    //     $userSubscription->payment_gateway = $request->deviceType === 'android' ? 'google' : 'apple';
    //     $userSubscription->subscribed_at = now();
    //     $userSubscription->expires_at = now()->addDays(30); // Assuming 30 days subscription
    //     $userSubscription->is_recurring = 1;
    //     $userSubscription->status = 'active';
    //     $userSubscription->created_at = now();

    //     if ($validationResult) {
    //         $userSubscription->transaction_id = $request->deviceType === 'android'
    //             ? $validationResult->getOrderId()
    //             : ($validationResult['receipt']['transaction_id'] ?? null);
    //     }
    //     $userSubscription->save();

    //     return $this->sendResponse([
    //         // 'subscription_id' => $userSubscription->id,
    //         // 'expires_at' => $userSubscription->expires_at
    //     ], 'Purchase verified and saved successfully');
    // }

    // public function savePurchaseLog(Request $request)
    // {
    //     $user = Auth::user();
    //     if (!$user) {
    //         return $this->sendError('Unauthorized', 401);
    //     }
    //     $purchaseLog = new PurchaseLog();
    //     $purchaseLog->user_id = $user->id;
    //     $purchaseLog->purchaseReceipt = json_encode($request->all());
    //     $purchaseLog->transaction_id = $request->get('purchaseReceipt');
    //     $purchaseLog->save();
    //     $requiredKeys = ['purchaseReceipt', 'secretKey', 'subscriptionType', 'deviceType', 'productId', 'price'];
    //     foreach ($requiredKeys as $key) {
    //         if (!$request->has($key)) {
    //             return $this->sendError("Missing required field: $key", null, 422);
    //         }
    //     }


    //     $validationResult = null;

    //     if ($request->deviceType === 'android') {
    //         $receiptData = [
    //             'packageName' => 'com.fitpro360',
    //             'productId' => $request->productId,
    //             'purchaseToken' => $request->purchaseReceipt
    //         ];

    //         $validationResult = $this->ValidateAndroidSubscriptionReceipt($receiptData);
    //         if (!$validationResult) {
    //             return $this->sendError('Invalid Android purchase receipt', null, 400);
    //         }

    //         if ($validationResult->getPaymentState() !== 1) {
    //             return $this->sendError('Android subscription payment not completed', null, 400);
    //         }
    //     } else if ($request->deviceType === 'ios') {

    //         $jwt = self::generateAppStoreJWT();
    //         if (!$jwt) {
    //             return $this->sendError('Unable to generate JWT for App Store', null, 500);
    //         }
    //         if (!$request->has('transactionId')) {
    //             return $this->sendError('Missing transactionId for iOS verification', null, 422);
    //         }
    //         $transactionId = $request->transactionId ?? null;
    //         $endpoints = [
    //             'production' => "https://api.storekit.itunes.apple.com/inApps/v1/transactions/{$transactionId}",
    //             'sandbox'    => "https://api.storekit-sandbox.itunes.apple.com/inApps/v1/transactions/{$transactionId}",
    //         ];
    //         $url = $endpoints['sandbox'];
    //         $ch = curl_init($url);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, [
    //             'Authorization: Bearer ' . $jwt,
    //             'Content-Type: application/json'
    //         ]);

    //         $response = curl_exec($ch);
    //         $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //         curl_close($ch);

    //         if ($httpCode !== 200 || !$response) {
    //             return $this->sendError('Failed to fetch transaction from Apple servers', null, 400);
    //         }

    //         $response = json_decode($response, true);

    //         if (isset($response['signedTransactionInfo'])) {
    //             $transactionPayload = self::decodeSignedTransactionInfo($response['signedTransactionInfo']);
    //             if ($transactionPayload) {
    //                 $productId = $transactionPayload['productId'] ?? '';
    //                 $purchaseDate = $transactionPayload['purchaseDate'] ?? '';
    //                 $expiresDate = $transactionPayload['expiresDate'] ?? '';
    //                 $transactionId = $transactionPayload['transactionId'] ?? '';

    //                 $validationResult = $transactionPayload;
    //             } else {
    //                 return $this->sendError('Invalid signed transaction payload', null, 400);
    //             }
    //         } else {
    //             return $this->sendError('Invalid response from App Store', null, 400);
    //         }
    //     } else {
    //         return $this->sendError('Invalid device type', null, 400);
    //     }

    //     $purchaseLog = new PurchaseLog();
    //     $purchaseLog->user_id = $user->id;
    //     $purchaseLog->purchaseReceipt = json_encode($request->all());
    //     $purchaseLog->transaction_id = $request->transactionId;

    //     if ($validationResult) {
    //         $purchaseLog->verification_data = json_encode($validationResult);
    //     }
    //     $purchaseLog->save();

    //     $existingSubscription = UserSubscription::where('user_id', $user->id)
    //         ->where('status', 'active')
    //         ->whereNull('deleted_at')
    //         ->orderByDesc('subscribed_at')
    //         ->first();

    //     // if ($existingSubscription) {
    //     //     if ($existingSubscription->subscription_id == $request->subscriptionType) {
    //     //         return $this->sendError('got auto renewal.', null, 409);
    //     //     } else {
    //     //         $existingSubscription->is_recurring = 0;
    //     //         $existingSubscription->expires_at = now();
    //     //         $existingSubscription->status = 'expired';
    //     //         $existingSubscription->save();
    //     //     }
    //     // }

    //     $userSubscription = new UserSubscription();
    //     $userSubscription->user_id = $user->id;
    //     $userSubscription->subscription_id = $request->subscriptionType;
    //     $userSubscription->payment_gateway = $request->deviceType === 'android' ? 'google' : 'apple';
    //     $userSubscription->subscribed_at = now();
    //     if ($validationResult) {
    //         if ($request->deviceType === 'android' && method_exists($validationResult, 'getExpiryTimeMillis')) {
    //             $expiryMillis = $validationResult->getExpiryTimeMillis();
    //             $userSubscription->expires_at = $expiryMillis ? Carbon::createFromTimestampMs($expiryMillis) : now()->addDays(30);
    //         } elseif ($request->deviceType === 'ios' && isset($validationResult['expiresDate'])) {
    //             $userSubscription->expires_at = Carbon::createFromTimestampMs($validationResult['expiresDate']);
    //         } else {
    //             $userSubscription->expires_at = now()->addDays(30);
    //         }
    //     } else {
    //         $userSubscription->expires_at = now()->addDays(30);
    //     }
    //     $userSubscription->is_recurring = 1;
    //     $userSubscription->status = 'active';
    //     $userSubscription->created_at = now();

    //     if ($validationResult) {
    //         $userSubscription->transaction_id = $request->deviceType === 'android'
    //             ? $validationResult->getOrderId()
    //             : ($validationResult['transactionId'] ?? null);

    //         if ($request->deviceType === 'ios' && isset($validationResult['expiresDate'])) {
    //             $userSubscription->expires_at = Carbon::createFromTimestampMs($validationResult['expiresDate']);
    //         }
    //     }

    //     $userSubscription->save();

    //     return $this->sendResponse(null, 'Purchase verified and saved successfully');
    // }

    public function savePurchaseLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactionId' => "required",
            'deviceType' => 'required|in:android,ios',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        $user = Auth::user();

        // Debug log (optional)
        DB::table('test')->insert([
            'data' => json_encode($request->all()),
        ]);

        $existingSubscription = null;

        if ($request->deviceType == 'ios') {
            $existingSubscription = UserSubscription::where('originalTransactionId', $request->transactionId)
                ->where('user_id', '!=', $user->id)
                ->where('status', 'active')
                ->first();
        }

        if ($request->deviceType == 'android') {
            $subscriptionDetailsResponse = $this->getSubscriptionDetailsv2('com.fitpro360', $request->purchaseReceipt);
            $subscriptionDetails = $subscriptionDetailsResponse instanceof \Illuminate\Http\JsonResponse
                ? $subscriptionDetailsResponse->getData(true)
                : $subscriptionDetailsResponse;

            //   $subscriptionDetailsResponse = $this->getSubscriptionDetailsv2('com.fitpro360', 'iokkgdfoihdbfbfibhpanmmg.AO-J1OxUHrnwWB-Tpi9RDd9B7aWAk9iRciuH5W7xUfU64KmLSSbHCokp8adh5ippfcSENLlKsQM1_mcAtsWQzgHXXuMGaBPE-Q');
            // $subscriptionDetails = $subscriptionDetailsResponse instanceof \Illuminate\Http\JsonResponse
            //     ? $subscriptionDetailsResponse->getData(true)
            //     : $subscriptionDetailsResponse;
        }

        $linkedToken = isset($subscriptionDetails['linkedPurchaseToken']) ? $subscriptionDetails['linkedPurchaseToken'] : null;
        $existingSubscription1 = null;
        $linkedUser = null;
        if ($linkedToken) {
            $existingSubscription1 = UserSubscription::where('originalTransactionId', $linkedToken)->first();
            if ($existingSubscription1) {

                $linkedUser = User::withTrashed()->find($existingSubscription1->user_id);
            }
        }


        // }


        // Find or create SubscribedOrgTransaction
        $transaction = SubscribedOrgTransaction::where('transactionId', $request->transactionId)->first();

        if (!$transaction) {
            $transaction = new SubscribedOrgTransaction();
            $transaction->user_id = $user->id;
            $transaction->save();

            if ($request->deviceType == 'ios') {
                $transaction->transactionId = $request->transactionId;
            } else {
                $transaction->transactionId = $request->purchaseReceipt; // Android
            }

            $transaction->deviceType = $request->deviceType;
            $transaction->request_payload = json_encode($request->all());
            $transaction->save();
        }


        // Process webhook data if any
        $pendingRecord = SubscriptionWebhook::where([
            'originalTransactionId' => $transaction->transactionId,
            'status' => 1
        ])->first();

        if ($pendingRecord) {
            $pendingRecord->status = 2;
            $pendingRecord->save();

            if ($request->deviceType == 'ios') {
                $webhookData = $this->getDataFromWebhookIos($pendingRecord, $request->transactionId, $user->id);
            }
        }

        if ($existingSubscription) {
            $linkedUser = User::withTrashed()->find($existingSubscription->user_id);

            //   pree($linkedUser);

            if ($linkedUser) {
                // Transfer subscription to current user
                $existingSubscription->update(['user_id' => $user->id]);
                // pree($existingSubscription->user_id);
                //update expires_at to now()->addDays(30);
                $existingSubscription->expires_at = now();
                // pree($existingSubscription->expires_at);
                $existingSubscription->save();

                // Update the transaction owner
                $transaction = SubscribedOrgTransaction::where('transactionId', $request->transactionId)->first();
                if ($transaction) {
                    $transaction->user_id = $user->id;
                    $transaction->save();
                }
                if ($linkedUser && !$linkedUser->trashed()) {
                    // Another active user owns the subscription
                    return response()->json([
                        'success' => true,
                        'message' => 'A subscription with this transaction already exists for another user.',
                        'data' => null
                    ], 409); // Conflict
                }

                // return response()->json([
                //     'success' => true,
                //     'message' => 'Subscription ownership successfully transferred.',
                //     'data' => null
                // ]);
            }
        }

        if ($existingSubscription1 && $existingSubscription1->user_id != $user->id) {

            // if ($linkedUser || $linkedUser->trashed()) {
            if ($linkedUser) {

                // Transfer subscription to current user
                // $existingSubscription1->update(['status' => 'deleted']); // TO NOT SEND IN GET SUBSCRIPTIONS

                $subscriptionPackagesData = SubscriptionPackages::where('product_id', $subscriptionDetailsResponse['lineItems'][0]['productId'])->first();
                // $existingSubscription1->expires_at = now();
                // $existingSubscription1->save();


                $userSubscription = new UserSubscription();
                $userSubscription->user_id = $user->id;
                $userSubscription->subscription_id = $subscriptionPackagesData->id;
                $userSubscription->payment_gateway = 'android';
                $userSubscription->subscribed_at = now();
                $userSubscription->expires_at = $subscriptionDetailsResponse['lineItems'][0]['expiryTime'];
                $userSubscription->is_recurring = 5;
                $userSubscription->amount = $subscriptionDetailsResponse['lineItems'][0]['autoRenewingPlan']['recurringPrice']['units'];
                $userSubscription->currency = $subscriptionDetailsResponse['lineItems'][0]['autoRenewingPlan']['recurringPrice']['currencyCode'];
                $userSubscription->status = 'active';
                $userSubscription->created_at = now();
                $userSubscription->originalTransactionId = $request->purchaseReceipt;
                $userSubscription->transaction_id = $request->transactionId;
                // $userSubscription->save(); // NOT NEEDED

                // } else {
                return response()->json([
                    'success' => true,
                    'message' => 'A subscription with this transaction already exists for another user.',
                    'data' => null
                ], 409); // Conflict
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Your subscription has been purchased successfully. Changes will apply soon — please allow a few moments for the update to appear.',
            'data' => null
        ]);
    }

    public function testResponse(Request $request)
    {
        $input = $request->all();

        $subscriptionDetailsResponse = $this->getSubscriptionDetailsv2('com.fitpro360', $input['purchaseReceipt']);
        $subscriptionDetails = $subscriptionDetailsResponse instanceof \Illuminate\Http\JsonResponse
            ? $subscriptionDetailsResponse->getData(true)
            : $subscriptionDetailsResponse;

        // pree($subscriptionDetailsResponse);
    }



    // public function replaceUserSubscription(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'consent' => 'required|boolean',
    //         'transactionId' => 'required|string', // Org transaction ID
    //         'userId' => 'required|integer',       // New user ID to transfer to
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     $currentUser = Auth::user();
    //     if (!$currentUser) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $transaction = SubscribedOrgTransaction::where('transactionId', $request->transactionId)->first();

    //     if (!$transaction) {
    //         return response()->json(['error' => 'Transaction not found'], 404);
    //     }

    //     $existingSubscription = UserSubscription::where('originalTransactionId', $request->transactionId)
    //         ->where('user_id', '!=', $request->userId)
    //         ->whereNull('deleted_at')
    //         ->first();

    //     if (!$existingSubscription) {
    //         return response()->json(['error' => 'No subscription found with this transaction id'], 404);
    //     }

    //     if ($request->consent) {
    //         // Transfer the subscription to new user
    //         $existingSubscription->user_id = $request->userId;
    //         $existingSubscription->save();

    //         // Update the transaction owner
    //         $transaction->user_id = $request->userId;
    //         $transaction->save();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription ownership successfully transferred.',
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Consent not provided for transfer.',
    //     ], 200);
    // } 

    // public function newReplaceUserSubscription(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'consent' => 'required|boolean',
    //         'transactionId' => 'required|string', // Org transaction ID
    //         'userId' => 'required|integer',       // New user ID to transfer to
    //         // 'deviceType' => 'required',       // New user ID to transfer to
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     $currentUser = Auth::user();
    //     if (!$currentUser) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     // $transaction = SubscribedOrgTransaction::where('transactionId', $request->transactionId)->first();

    //     // if (!$transaction) {
    //     //     return response()->json(['error' => 'Transaction not found'], 404);
    //     // }


    //     // if($request->deviceType == 'ios') {
    //     //     $transaction->deviceType = 'ios';
    //     // } else {
    //     //     $transaction->deviceType = 'android';
    //     // }
    //     $existingSubscription = UserSubscription::where('originalTransactionId', $request->transactionId)
    //         ->where('user_id', '!=', $request->userId)
    //         ->whereNull('deleted_at')
    //         ->first();

    //     if ($request->deviceType == 'android') {
    //         $subscriptionDetailsResponse = $this->getSubscriptionDetailsv2('com.fitpro360', $request->transactionId);
    //         $subscriptionDetails = $subscriptionDetailsResponse instanceof \Illuminate\Http\JsonResponse
    //             ? $subscriptionDetailsResponse->getData(true)
    //             : $subscriptionDetailsResponse;

    //         $linkedToken = isset($subscriptionDetails['linkedPurchaseToken']) ? $subscriptionDetails['linkedPurchaseToken'] : null;
    //         pree($linkedToken);
    //         if ($linkedToken) {
    //             $existingSubscription = UserSubscription::where('originalTransactionId', $linkedToken)->first();
    //             if ($existingSubscription) {

    //                 if ($request->consent) {
    //                     $existingSubscription->user_id;

    //                     $existingSubscription->save();

    //                     // Update the transaction owner
    //                     $transaction->user_id = $request->userId;
    //                     $transaction->save();

    //                     return response()->json([
    //                         'success' => true,
    //                         'message' => 'Subscription ownership successfully transferred.',
    //                     ]);
    //                 }
    //                 return response()->json([
    //                     'success' => true,
    //                     'message' => 'Consent not provided for transfer.',
    //                 ], 200);}
    //         }
    //     }


    //     // pree($request->transactionId, 's');
    //     // pree($existingSubscription);


    //     if (!$existingSubscription) {
    //         return response()->json(['error' => 'No subscription found with this transaction id'], 404);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Consent not provided for transfer.',
    //     ], 200);
    // }

    public function replaceUserSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consent' => 'required|boolean',
            'transactionId' => 'required|string', // Android purchaseToken
            'userId' => 'required|integer',
            'deviceType' => 'required|in:android,ios',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $transaction = SubscribedOrgTransaction::where('transactionId', $request->transactionId)->first();
        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $originalTransactionId = $request->transactionId;

        // If Android, get linked purchase token
        if ($request->deviceType === 'android') {
            $subscriptionDetailsResponse = $this->getSubscriptionDetailsv2('com.fitpro360', $request->transactionId);
            $subscriptionDetails = $subscriptionDetailsResponse instanceof \Illuminate\Http\JsonResponse
                ? $subscriptionDetailsResponse->getData(true)
                : $subscriptionDetailsResponse;

            if (!isset($subscriptionDetails['linkedPurchaseToken'])) {
                return response()->json(['error' => 'No linked purchase token found'], 404);
            }

            $originalTransactionId = $subscriptionDetails['linkedPurchaseToken'];
        }

        if ($request->deviceType === 'ios') {
            $originalTransactionId = $request->transactionId;
        }

        $existingSubscription = UserSubscription::where('originalTransactionId', $originalTransactionId)
            ->where('user_id', '!=', $request->userId)
            ->whereNull('deleted_at') // Ensures subscription is not soft-deleted
            ->whereHas('user', function ($query) {
                $query->whereNull('deleted_at'); // Ensures associated user is not soft-deleted
            })
            ->first();
        // pree($existingSubscription);
        if (!$existingSubscription) {
            return response()->json(['error' => 'No subscription found with this transaction id'], 404);
        }
        // Get latest subscription of the logged-in user
        $latestSubscriptionOfLoggedInUser = UserSubscription::where('user_id', $currentUser->id)
            ->whereNull('deleted_at')
            ->latest()
            ->first();
        //  pree($latestSubscriptionOfLoggedInUser);
        if ($request->consent) {
            // User consented — assume transfer is already done before
            return response()->json([
                'success' => true,
                'message' => 'Subscription ownership transfer done and consented.',
            ]);
        } else {
            // Revert the ownership transfer
            if ($latestSubscriptionOfLoggedInUser) {
                $latestSubscriptionOfLoggedInUser->user_id = $existingSubscription->user_id;
                $latestSubscriptionOfLoggedInUser->save();

                // Also revert in transaction table
                if ($transaction) {
                    $transaction->user_id = $existingSubscription->user_id;
                    $transaction->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription transfer reverted to previous user due to lack of consent.',
                ]);
            } elseif (!$existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existing subscription found to revert.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Previous subscription owner not found. Cannot revert.',
            ]);
        }
    }

    public static function ValidateAndroidSubscriptionReceipt($receiptData = [])
    {
        if (empty($receiptData)) return false;

        try {
            $client = new Google_Client();
            $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
            $client->addScope(Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
            $service = new Google_Service_AndroidPublisher($client);

            return $service->purchases_subscriptions->get(
                $receiptData['packageName'],
                $receiptData['productId'],
                $receiptData['purchaseToken']
            );
        } catch (\Google\Service\Exception $e) {
            Log::error("Google API error: " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            Log::error("Android validation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel an Android subscription.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function cancelAndroidSubscription(Request $request)
    // {
    //     // Get the current user
    //     try {
    //         $user = Auth::user();
    //         if (!$user) {
    //             return response()->json(['error' => 'Unauthorized'], 401);
    //         }

    //         $subscription = UserSubscription::where('user_id', $user->id)
    //             ->where('status', 'active')
    //             ->whereNull('deleted_at')
    //             ->orderByDesc('subscribed_at')
    //             ->first();

    //         if (!$subscription) {
    //             return response()->json(['error' => 'No active subscription found.'], 404);
    //         }

    //         $purchaseLog = PurchaseLog::where('user_id', $user->id)
    //             ->orderByDesc('created_at')
    //             ->first();

    //         if (!$purchaseLog) {
    //             return response()->json(['error' => 'No purchase log found.'], 404);
    //         }

    //         $receipt = json_decode($purchaseLog->purchaseReceipt, true);

    //         $receiptData = [
    //             'packageName'   => 'com.fitpro360',
    //             'productId'     => $receipt['productId'] ?? null,
    //             'purchaseToken' => $receipt['purchaseReceipt'] ?? null,
    //             // 'purchaseToken' => $receipt['purchaseToken'] ?? $receipt['transactionId'] ?? null,

    //         ];
    //         // Basic validation
    //         if (empty($receiptData['packageName']) || empty($receiptData['productId']) || empty($receiptData['purchaseToken'])) {
    //             return response()->json(['error' => 'Missing required fields.'], 422);
    //         }
    //         // Setup Google Client
    //         $client = new \Google_Client();
    //         $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
    //         $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
    //         $service = new Google_Service_AndroidPublisher($client);

    //         // Cancel subscription
    //         $service->purchases_subscriptions->cancel(
    //             $receiptData['packageName'],
    //             $receiptData['productId'],
    //             $receiptData['purchaseToken']
    //         );
    //         \Log::info("Android subscription cancelled successfully", [
    //             'user_id' => auth()->id(),
    //             'receipt' => $receiptData
    //         ]);

    //         $existingSubscription = UserSubscription::where('user_id', $user->id)
    //             ->where('status', 'active')
    //             ->whereNull('deleted_at')
    //             ->orderByDesc('subscribed_at')
    //             ->first();

    //         if ($existingSubscription) {
    //             $existingSubscription->status = 'cancelled';
    //             // $existingSubscription->deleted_at = now();
    //             $existingSubscription->save();
    //         }

    //         return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully.']);
    //     } catch (\Google\Service\Exception $e) {
    //         \Log::error("Google Play API Error cancelling subscription: " . $e->getMessage());
    //         return response()->json(['error' => 'Google API error', 'message' => $e->getMessage()], 500);
    //     } catch (\Exception $e) {
    //         \Log::error("Unexpected error cancelling subscription: " . $e->getMessage());
    //         return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 500);
    //     }
    // }


    public function getUserSubscriptions(Request $request)
    {
        $user = Auth::user();
        $lockKey = "user:{$user->id}:check-user-mail";
        if (!$user) {
            return $this->sendError('Unauthorized', 401);
        }

        $now = Carbon::now();
        $lock = Cache::get($lockKey);

        // Fetch latest subscription where expires_at >= now
        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('expires_at', '>=', $now)
            ->orderByDesc('id')
            ->orderByDesc('expires_at')
            // status must not be cancelled
            // ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'expired')
            ->limit(1)
            ->get();

        $subscription = $subscriptions->first();

        // First check if user has any active subscription
        $hasActiveSubscription = UserSubscription::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveSubscription) {
            // Check for expired subscriptions first
            $expiredSubscription = UserSubscription::where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->where('status', 'expired')
                ->where('is_expire_email_sent', null)
                ->orderByDesc('id')
                ->first();

            if ($expiredSubscription && !empty($user->email)) {
                if (!$lock) {
                    Cache::put($lockKey, true, 120);
                    try {
                        // Update the email sent status
                        $expiredSubscription->is_expire_email_sent = 1;
                        $expiredSubscription->save();

                        Mail::to($user->email)->send(new SubscriptionExpireMail($user, $expiredSubscription->expires_at));

                        Log::info("Expired subscription email sent to user ID {$user->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send expiration email to user ID {$user->id}: " . $e->getMessage());
                    }
                } else {
                    Log::info("Skipping duplicate mail send for user {$user->id}");
                }
            } elseif (empty($user->email)) {
                Log::warning("User ID {$user->id} has no email address. Cannot send expiration mail.");
            }

            Log::info("Cancelled expires check started for user ID {$user->id} ====" . now());

            // Check for cancelled subscriptions with future expiration date
            $cancelledSubscription = UserSubscription::where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->where('status', 'cancelled')
                ->where('expires_at', '<', now())
                ->where('is_expire_email_sent', null)
                ->orderByDesc('id')
                ->first();

            if ($cancelledSubscription && !empty($user->email)) {
                if (!$lock) {
                    Cache::put($lockKey, true, 120);
                    try {
                        // Update the email sent status
                        $cancelledSubscription->is_expire_email_sent = 1;
                        $cancelledSubscription->save();

                        Mail::to($user->email)->send(new SubscriptionExpireMail($user, $cancelledSubscription->expires_at));

                        Log::info("Cancelled expires subscription email sent to user ID {$user->id} ====" . now());
                    } catch (\Exception $e) {
                        Log::error(now() . "======Failed to send cancellation email to user ID {$user->id}: " . $e->getMessage());
                    }
                } else {
                    Log::info("Skipping duplicate mail send for user {$user->id}");
                }
            }
        }

        if (!$subscription) {
            return $this->sendResponse(null, 'No subscriptions found for this user.', 200);
        }

        $subscriptions->transform(function ($sub) {
            if ($sub->payment_gateway === 'android') {
                $sub->amount = $sub->amount;
                // $sub->amount = $sub->amount / 1000000;
            } elseif ($sub->payment_gateway === 'ios') {
                $sub->amount = $sub->amount / 1000;
            }
            return $sub;
        });

        return $this->sendResponse($subscriptions, 'User subscriptions retrieved successfully.');
    }

    public function testverifyreceipt1()
    {
        Log::info("Subscription expiry check for users started.");
        Log::info("CRON START: Subscription expiry check started at " . now());

        $subscriptions = UserSubscription::where('expires_at', '<', now())
            ->where('status', 'active')
            ->get();


        foreach ($subscriptions as $subscription) {
            $userId = $subscription->user_id;
            Log::info("Checking subscription for user ID: {$userId}");

            $purchaseLog = PurchaseLog::where('user_id', $userId)
                ->latest()
                ->first();

            if ($purchaseLog && $purchaseLog->verification_data) {
                Log::info("Found purchase log for user ID {$userId}: " . json_encode($purchaseLog->verification_data));

                $verification = json_decode($purchaseLog->verification_data, true);
                $expiry = null;

                // Handle iOS
                if (
                    isset($verification['type']) &&
                    $verification['type'] === 'Auto-Renewable Subscription' &&
                    isset($verification['expiresDate'])
                ) {
                    // ->setTimezone('Asia/Kolkata');
                    $expiry = Carbon::createFromTimestampMs($verification['expiresDate']);
                    Log::info("Parsed iOS expiry date (IST) for user ID {$userId}: " . $expiry);
                }

                // Handle Android
                if (
                    isset($verification['autoRenewing']) &&
                    $verification['autoRenewing'] === true &&
                    isset($verification['expiryTimeMillis'])
                ) {
                    $expiry = Carbon::createFromTimestampMs($verification['expiryTimeMillis']);
                    Log::info("Parsed Android expiry date (IST) for user ID {$userId}: " . $expiry);
                }

                if (!empty($expiry) && $expiry->gt(now())) {
                    $subscription->expires_at = $expiry;
                    $subscription->status = 'active';
                    $subscription->save();

                    $this->info("User ID {$userId} auto-renewed. New expiry: {$expiry->toDateTimeString()}.");
                    Log::info("Subscription renewed for user ID {$userId}. New expiry: {$expiry->toDateTimeString()}.");
                    continue;
                } else {
                    Log::info("No valid renewal detected for user ID {$userId}. Current expiry: " . ($expiry ? $expiry->toDateTimeString() : 'null'));
                }
            } else {
                Log::info("No valid purchase log found for user ID {$userId}.");
            }

            // Mark as expired
            $subscription->status = 'expired';
            $subscription->save();

            $this->info("Subscription for user ID {$userId} has expired and is now marked as 'expired'.");
            Log::info("Subscription marked as expired for user ID {$userId}.");
        }

        $this->info("Subscription check completed.");
        Log::info("CRON END: Subscription expiry check finished at " . now('Asia/Kolkata'));
    }

    public function cancelAndroidSubscription(Request $request)
    {
        // DB::table('test')->insert([
        //     'data' => json_encode($request->all()),
        // ]);


        // Get the current user
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->orderByDesc('subscribed_at')
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'No active subscription found.'], 404);
            }

            // TO GET PURCHASE RECEIPT
            // $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first(); // transactionId = org transaction id

            // $receipt = json_decode($subscription->originalTransactionId, true);

            $receiptData = [
                'packageName'   => env('PACKAGE_NAME') ?? "com.fitpro360",
                'productId'     => $subscription->transaction_id ?? null,
                'purchaseToken' => $subscription->originalTransactionId ?? null,
                // 'purchaseToken' => $receipt['purchaseToken'] ?? $receipt['transactionId'] ?? null,
            ];


            // Validation
            if (empty($receiptData['packageName']) || empty($receiptData['productId']) || empty($receiptData['purchaseToken'])) {
                return response()->json(['error' => 'Missing required fields.'], 422);
            }
            // Setup Google Client
            $client = new \Google_Client();
            $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
            $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
            $service = new Google_Service_AndroidPublisher($client);

            // Cancel subscription
            $service->purchases_subscriptions->cancel(
                $receiptData['packageName'],
                $receiptData['productId'],
                $receiptData['purchaseToken']
            );
            Log::info("Your Android subscription is being cancelled successfully. Please do not close the app — this may take a few moments", [
                'user_id' => auth()->id(),
                'receipt' => $receiptData
            ]);

            $subscription->status = 'cancelled';
            $subscription->save();

            if ($user->deleted_at) {
                $client = new \Google_Client();
                $client->setAuthConfig(base_path(env('GOOGLE_PLAY_JSON_KEY_PATH')));
                $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);
                $service = new \Google_Service_AndroidPublisher($client);

                $service->purchases_subscriptions->cancel(
                    $receiptData['packageName'],
                    $receiptData['productId'],
                    $receiptData['purchaseToken']
                );
                $subscription->status = 'cancelled';
                $subscription->save();
                // return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully, due to user account deletion.']);
                Log::info("Subscription cancelled successfully, due to user account deletion.", [
                    'user_id' => auth()->id(),
                    'receipt' => $receiptData
                ]);
            }
            // $existingSubscription = UserSubscription::where('user_id', $user->id, 'originalTransactionId', $receiptData['purchaseToken'])
            //     ->where('status', 'active')
            //     ->whereNull('deleted_at')
            //     ->orderByDesc('subscribed_at')
            //     ->first();

            // if ($existingSubscription) {
            //     $existingSubscription->status = 'cancelled';
            //     // $existingSubscription->deleted_at = now();
            //     $existingSubscription->save();
            // }

            return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully.']);
        } catch (\Google\Service\Exception $e) {
            Log::error("Google Play API Error cancelling subscription: " . $e->getMessage());
            return response()->json(['error' => 'Google API error', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error("Unexpected error cancelling subscription: " . $e->getMessage());
            return response()->json(['error' => 'Unexpected error', 'message' => $e->getMessage()], 422);
        }
    }


    // WEBHOOKS

    public function androidSubscriptionsWebhook(Request $request)
    {
        // $webhookData1 = new SubscriptionWebhook();
        // $webhookData1->originalTransactionId = '000000';
        // $webhookData1->data = json_encode($request->all());
        // $webhookData1->type = '000000';
        // $webhookData1->created_at = now();
        // $webhookData1->status = 2;
        // $webhookData1->save();

        $webhookArray = $request->all();
        // $webhookArray = json_decode(file_get_contents("php://input"), true);
        // $webhookArray = json_decode($webhookRawJson, true);

        $base64Decode = base64_decode($webhookArray['message']['data']);

        $base64Decode = json_decode($base64Decode, true);

        // response('OK', 200)->send();


        $transaction_type = [2, 3, 4, 13];

        // Check if notificationType exists 
        if (isset($base64Decode['subscriptionNotification']['notificationType']) && in_array($base64Decode['subscriptionNotification']['notificationType'], $transaction_type)) { // NEW PURCHASED


            $originalTransactionId = $base64Decode['subscriptionNotification']['purchaseToken'] ?? null;

            if ($originalTransactionId) {

                // $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first(); // transactionId = org transaction id

                $maxRetries = 7;
                $retryDelay = 2;
                $subscribedOrgTransactionData = null;
                for ($i = 0; $i < $maxRetries; $i++) {
                    $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first();

                    if ($subscribedOrgTransactionData) {
                        break;
                    }
                    sleep($retryDelay);
                }



                try {
                    if ($subscribedOrgTransactionData) {

                        $webhookData = new SubscriptionWebhook();
                        $webhookData->originalTransactionId = $originalTransactionId;
                        $webhookData->notification_type = strtolower($base64Decode['subscriptionNotification']['notificationType']);
                        $webhookData->data = json_encode($request->all());
                        $webhookData->type = 'android';
                        $webhookData->created_at = now();
                        $webhookData->status = 2;
                        $webhookData->save();




                        $subscriptionPackagesData = SubscriptionPackages::where('product_id', $base64Decode['subscriptionNotification']['subscriptionId'])->first();

                        if ($base64Decode['subscriptionNotification']['notificationType'] == 2) {
                            $transaction_type = 2;
                        } elseif ($base64Decode['subscriptionNotification']['notificationType'] == 4) {
                            $transaction_type = 1;
                        }
                        // $subscriptionDetail = $this->getAndroidSubscriptionDetails($base64Decode['packageName'], $base64Decode['subscriptionNotification']['subscriptionId'], $base64Decode['subscriptionNotification']['purchaseToken']);

                        // if ($base64Decode['subscriptionNotification']['notificationType'] == 2 || $base64Decode['subscriptionNotification']['notificationType'] == 4) {

                        //     UserSubscription::where('originalTransactionId', $originalTransactionId)->update(['status' => 'expired']); // FIRST EXPIRED OLD DATA

                        //     $userSubscription = new UserSubscription();
                        //     $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
                        //     $userSubscription->transaction_id = $base64Decode['subscriptionNotification']['subscriptionId'];
                        //     $userSubscription->originalTransactionId = $originalTransactionId;
                        //     $userSubscription->subscription_id = $subscriptionPackagesData->id;
                        //     $userSubscription->payment_gateway = 'android';
                        //     $userSubscription->subscribed_at =  (int) ($subscriptionDetail['startTimeMillis'] / 1000);
                        //     $userSubscription->expires_at = (int) ($subscriptionDetail['expiryTimeMillis'] / 1000);
                        //     $userSubscription->is_recurring = 1;
                        //     $userSubscription->amount = $subscriptionDetail['priceAmountMicros'];
                        //     $userSubscription->transaction_type = $transaction_type;
                        //     $userSubscription->currency = $subscriptionDetail['priceCurrencyCode'];
                        //     $userSubscription->status = 'active';
                        //     $userSubscription->save();

                        //     return response('OK', 200);
                        // } 
                        $packageName     = $base64Decode['packageName'];
                        $subscriptionId  = $base64Decode['subscriptionNotification']['subscriptionId'];
                        $purchaseToken   = $base64Decode['subscriptionNotification']['purchaseToken'];

                        // Add a debug log here to be sure
                        Log::info('Decoded Android webhook data', [
                            'packageName'    => $packageName,
                            'subscriptionId' => $subscriptionId,
                            'purchaseToken'  => $purchaseToken,
                        ]);

                        $subscriptionDetail = $this->getSubscriptionDetailsv2(
                            $packageName,
                            // $subscriptionId,
                            $purchaseToken
                        );


                        if (
                            $base64Decode['subscriptionNotification']['notificationType'] == 2 || // RENEWED
                            $base64Decode['subscriptionNotification']['notificationType'] == 4    // PURCHASED / UPGRADED / DOWNGRADED
                        ) {
                            if (!empty($subscriptionDetail['linkedPurchaseToken'])) {
                                // Find the previous subscription by linked token
                                UserSubscription::where('originalTransactionId', $subscriptionDetail['linkedPurchaseToken'])
                                    ->update(['status' => 'expired']);
                            } else {
                                // fallback if no linked token
                            }
                            UserSubscription::where('originalTransactionId', $originalTransactionId)
                                ->update(['status' => 'expired']);

                            // $previousSubscription = '';
                            // if(isset($subscriptionDetail['linkedPurchaseToken'])){
                            //     $previousSubscription = UserSubscription::where('originalTransactionId', $subscriptionDetail['linkedPurchaseToken'])->orderBy('created_at', 'desc')->first();
                            // }


                            Log::info('Handling subscription upgrade/downgrade', [
                                'new_token' => $purchaseToken,
                                'linked_token' => $subscriptionDetail['linkedPurchaseToken'] ?? null
                            ]);

                            $lineItem = $subscriptionDetail['lineItems'][0] ?? null;

                            if (!$lineItem) {
                                Log::error("Line item missing in subscription detail", ['detail' => $subscriptionDetail]);
                                return response('Line item missing', 500);
                            }

                            $recurringPrice = $lineItem['autoRenewingPlan']['recurringPrice'] ?? null;

                            if (!$recurringPrice) {
                                Log::error("Recurring price info missing in line item", ['lineItem' => $lineItem]);
                                return response('Price info missing', 500);
                            }

                            $userSubscription = new UserSubscription();
                            $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
                            $userSubscription->transaction_id = $lineItem['latestSuccessfulOrderId'] ?? null;
                            // Save linkedPurchaseToken if available
                            // if (!empty($subscriptionDetail['linkedPurchaseToken'])) {
                            // $userSubscription->originalTransactionId = $subscriptionDetail['linkedPurchaseToken'];
                            // } else {

                            $userSubscription->originalTransactionId = $originalTransactionId; // do not overwrite
                            // }
                            // $userSubscription->purchase_token = $base64Decode['subscriptionNotification']['purchaseToken']; // REMOVE THIS
                            $userSubscription->subscription_id = $subscriptionPackagesData->id;
                            $userSubscription->payment_gateway = 'android';
                            $userSubscription->subscribed_at = strtotime($subscriptionDetail['startTime']);
                            $userSubscription->expires_at = strtotime($lineItem['expiryTime']);
                            $userSubscription->is_recurring = $lineItem['autoRenewingPlan']['autoRenewEnabled'] ?? 0;
                            $userSubscription->amount = $recurringPrice['units'] ?? 0;
                            $userSubscription->currency = $recurringPrice['currencyCode'] ?? 'INR';
                            $userSubscription->transaction_type = $transaction_type;
                            $userSubscription->status = 'active';
                            $userSubscription->save();


                            return response('OK', 200);
                        } else if ($base64Decode['subscriptionNotification']['notificationType'] == 3 || $base64Decode['subscriptionNotification']['notificationType'] == 13) {
                            $userSubscription = UserSubscription::where('originalTransactionId', $originalTransactionId)->where('user_id', $subscribedOrgTransactionData->user_id)->where('status', 'active')->latest()->first();

                            if ($userSubscription) {

                                if ($base64Decode['subscriptionNotification']['notificationType'] == 3) {
                                    $status = 'cancelled';
                                } elseif ($base64Decode['subscriptionNotification']['notificationType'] == 13) {
                                    $status = 'expired';
                                } elseif ($base64Decode['subscriptionNotification']['notificationType'] == 12) {
                                    $status = 'revoked';
                                }

                                $userSubscription->status = $status;
                                $userSubscription->save();


                                return response('OK', 200);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error saving purchase receipt at line00000000000 " . $e->getLine() . " in file " . $e->getFile() . ": " . $e->getMessage());
                    response('OK', 200)->send();
                }
            }
        }
        response('OK', 200)->send();
    }

    public function iosSubscriptionsWebhook(Request $request)
    {
        $webhookData = new SubscriptionWebhook();

        $webhookData->data = json_encode($request->all());
        $webhookData->type = 'ios';
        $webhookData->created_at = now();

        $webhookArray = json_decode($webhookData->data);
        $jwtDecode = self::decodeSignedTransactionInfo($webhookArray->signedPayload);

        $webhookData->notification_type = strtolower($jwtDecode['notificationType']);

        // $webhookData->save();

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

                        // Only consider SubscribedOrgTransaction where the associated user is not soft-deleted
                        // transactionId refers to original transaction ID
                        $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)
                            ->whereHas('user', function ($query) {
                                $query->whereNull('deleted_at');
                            })
                            ->first();
                        // pree($subscribedOrgTransactionData);

                        if ($subscribedOrgTransactionData) {

                            $webhookData->status = 2;
                            $webhookData->save();

                            $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                            $userSubscription = new UserSubscription();
                            $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
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

                            return response('Process done', 200);
                        }
                    }
                } elseif ($status == 2) {
                    // Handle cancelled/expired subscription
                } else {
                    // Handle any other status if needed
                }
            } else {
                // 'status' not present — handle gracefully
            }
        } else if (isset($jwtDecode['notificationType']) && strtolower($jwtDecode['notificationType']) === 'did_renew') {

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

                        $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)
                            ->whereHas('user', function ($query) {
                                $query->whereNull('deleted_at');
                            })
                            ->first(); // transactionId = org transaction id

                        if ($subscribedOrgTransactionData) {

                            $webhookData->status = 2;
                            $webhookData->save();

                            UserSubscription::where('originalTransactionId', $originalTransactionId)->where('status', 'active')->update(['status' => 'expired']);

                            $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                            $userSubscription = new UserSubscription();
                            $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
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
                            $userSubscription->transaction_type = 2;
                            $userSubscription->save();

                            return response('Process done', 200);
                        }
                    }
                }
            }
        } else if (isset($jwtDecode['notificationType']) && strtolower($jwtDecode['notificationType']) === 'did_change_renewal_status') { // CANCEL SUSCRIPTION
            if (isset($jwtDecode['subtype']) && strtolower($jwtDecode['subtype']) === 'auto_renew_disabled') {
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

                            $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first(); // transactionId = org transaction id



                            if ($subscribedOrgTransactionData) {

                                $webhookData->status = 2;
                                $webhookData->save();

                                UserSubscription::where('originalTransactionId', $originalTransactionId)->where('status', 'active')->update(['status' => 'expired']);


                                $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                                $userSubscription = UserSubscription::where('originalTransactionId', $originalTransactionId)->latest()->first();


                                $userSubscription->status = 'cancelled';
                                $userSubscription->save();

                                return response('Process done', 200);
                            }
                        }
                    } elseif ($status == 2) {
                        // Handle expired subscription
                    }
                }
            }
        } else if (isset($jwtDecode['notificationType']) && strtolower($jwtDecode['notificationType']) === 'expired') { // EXPIRED SUSCRIPTION


            // if (isset($jwtDecode['subtype']) && strtolower($jwtDecode['subtype']) === 'auto_renew_disabled') {
            if (isset($jwtDecode['data']['status'])) {
                $status = $jwtDecode['data']['status'];
                if ($status == 2) {
                    // Handle active subscription
                    $signedTransactionInfoJWT = $jwtDecode['data']['signedTransactionInfo'];

                    // Decode JWT (no signature validation here, for Apple JWT use their public key if needed)
                    $signedPayloadJwtDecode = self::decodeSignedTransactionInfo($signedTransactionInfoJWT);

                    $originalTransactionId = $signedPayloadJwtDecode['originalTransactionId'] ?? null;

                    if ($originalTransactionId) {
                        $webhookData->originalTransactionId = $originalTransactionId;
                        $webhookData->save();

                        $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)->first(); // transactionId = org transaction id

                        if ($subscribedOrgTransactionData) {

                            $webhookData->status = 2;
                            $webhookData->save();

                            UserSubscription::where('originalTransactionId', $originalTransactionId)->where('status', 'active')->update(['status' => 'expired']);

                            $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                            $userSubscription = UserSubscription::where('originalTransactionId', $originalTransactionId)->latest()->first();

                            $userSubscription->status = 'expired';
                            $userSubscription->save();

                            return response('Process done', 200);
                        }
                    }
                }
            }
            // }
        } else if (isset($jwtDecode['notificationType']) && strtolower($jwtDecode['notificationType']) === 'did_change_renewal_pref') { // UPGRADE SUSCRIPTION
            if (isset($jwtDecode['subtype']) && strtolower($jwtDecode['subtype']) === 'upgrade') {
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

                            $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)
                                ->whereHas('user', function ($query) {
                                    $query->whereNull('deleted_at');
                                })
                                ->first();
                            if ($subscribedOrgTransactionData) {

                                $webhookData->status = 2;
                                $webhookData->save();

                                UserSubscription::where('originalTransactionId', $originalTransactionId)->where('status', 'active')->update(['status' => 'expired']);

                                $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                                $userSubscription = new UserSubscription();
                                $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
                                $userSubscription->transaction_id = $signedPayloadJwtDecode['transactionId'];
                                $userSubscription->originalTransactionId = $originalTransactionId;
                                $userSubscription->subscription_id = $subscriptionPackagesData->id;
                                $userSubscription->payment_gateway = 'ios';
                                $userSubscription->subscribed_at =  (int) ($signedPayloadJwtDecode['purchaseDate'] / 1000); // REMOVING LAST THREE DIGIT FROM TIMESTAMP
                                $userSubscription->expires_at = (int) ($signedPayloadJwtDecode['expiresDate'] / 1000); // REMOVING LAST THREE DIGIT FROM TIMESTAMP
                                $userSubscription->is_recurring = 1;
                                $userSubscription->amount = $signedPayloadJwtDecode['price'];
                                $userSubscription->currency = $signedPayloadJwtDecode['currency'];
                                $userSubscription->status = 'active';
                                $userSubscription->transaction_type = 4;
                                $userSubscription->save();

                                return response('Process done', 200);
                            }
                        }
                    }
                }
            }

            if (isset($jwtDecode['subtype']) && strtolower($jwtDecode['subtype']) === 'downgrade') {
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

                            $subscribedOrgTransactionData = SubscribedOrgTransaction::where('transactionId', $originalTransactionId)
                                ->whereHas('user', function ($query) {
                                    $query->whereNull('deleted_at');
                                })
                                ->first();
                            if ($subscribedOrgTransactionData) {

                                $webhookData->status = 2;
                                $webhookData->save();

                                UserSubscription::where('originalTransactionId', $originalTransactionId)
                                    ->update(['status' => 'expired']);

                                $subscriptionPackagesData = SubscriptionPackages::where('product_id', $signedPayloadJwtDecode['productId'])->first();

                                $userSubscription = new UserSubscription();
                                $userSubscription->user_id = $subscribedOrgTransactionData->user_id;
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
                                $userSubscription->transaction_type = 3;
                                $userSubscription->save();

                                return response('Process done', 200);
                            }
                        }
                    }
                }
            }
        }
    }


    public function iosSubscriptionRestore(Request $request)
    {
        if (!$request->has('purchaseReceipt') || empty($request->purchaseReceipt)) {
            return $this->sendError('Purchase receipt is required', [], 422);
        }

        $jwtDecode = self::decodeSignedTransactionInfo($request->purchaseReceipt);

        if (empty($jwtDecode) || !is_array($jwtDecode) || !isset($jwtDecode['originalTransactionId'])) {
            return $this->sendError('Invalid purchase receipt data', [], 400);
        }

        $userSubscription = UserSubscription::where([
            'originalTransactionId' => $jwtDecode['originalTransactionId'],
            'status' => 'active'
        ])->first();

        if ($userSubscription) {
            $linkedUser = User::withTrashed()->find($userSubscription->user_id);

            if ($linkedUser && !$linkedUser->trashed()) {
                // Active user exists — block restore
                return $this->sendError(
                    'You have already purchased a plan from a different account. Please login from that account to use this feature.',
                    [],
                    400
                );
            } elseif ($linkedUser && $linkedUser->trashed()) {
                // Soft-deleted user — allow transfer
                $userSubscription->update(['user_id' => Auth::id()]);
                // create new entry in org table
                $subscribedOrgTransaction = new SubscribedOrgTransaction();
                $subscribedOrgTransaction->transactionId = $jwtDecode['originalTransactionId'];
                $subscribedOrgTransaction->user_id = Auth::id();
                $subscribedOrgTransaction->deviceType = 'ios';
                //request_payload
                $subscribedOrgTransaction->request_payload = json_encode($request->all());
                $subscribedOrgTransaction->save();

                return $this->sendResponse(null, 'Subscription restored successfully to your account.');
            }
        }

        // If no data in UserSubscription, check in fallback tables
        if (!$userSubscription) {
            $subscribedOrgTransaction = SubscribedOrgTransaction::where('transactionId', $jwtDecode['originalTransactionId'])->first();

            if (!$subscribedOrgTransaction) {
                $subscribedwebhook = SubscriptionWebhook::where('originalTransactionId', $jwtDecode['originalTransactionId'])->first();
            }

            if (!$subscribedOrgTransaction && !$subscribedwebhook) {
                return $this->sendError(null, 'No active subscription found for this account', 404);
            }
        }

        // At this point, either:
        // - the subscription exists but linked user was deleted
        // - or it's a valid restore attempt from webhook/transactions

        return $this->sendResponse([], 'No plan is active on this account');
    }
    public function androidSubscriptionRestore(Request $request)
    {
        if (!$request->has('purchaseReceipt') || empty($request->purchaseReceipt)) {
            return $this->sendError('Purchase receipt is required', [], 422);
        }

        $purchaseToken = $request->purchaseReceipt;
        // Step 1: Try finding UserSubscription by purchase token
        $userSubscription = UserSubscription::where([
            'originalTransactionId' => $purchaseToken,
            'status' => 'active'
        ])->first();

        if ($userSubscription) {
            $linkedUser = User::withTrashed()->find($userSubscription->user_id);

            if ($linkedUser) {
                if (!$linkedUser->trashed()) {
                    return $this->sendError(
                        'You have already purchased a plan from a different account. Please login from that account to use this feature.',
                        [],
                        400
                    );
                } else {
                    $userSubscription->update(['user_id' => Auth::id()]);
                    return $this->sendResponse(null, 'Subscription restored successfully to your account.');
                }
            }
        }

        // Step 2: Fallback search in raw webhook or transaction logs
        $subscribedOrgTransaction = SubscribedOrgTransaction::where('transactionId', $purchaseToken)->first();
        $subscribedWebhook = null;

        if (!$subscribedOrgTransaction) {
            $subscribedWebhook = SubscriptionWebhook::where('originalTransactionId', $purchaseToken)->first();
        }

        if (!$subscribedOrgTransaction && !$subscribedWebhook) {
            return $this->sendError(null, 'No active subscription found for this account', 404);
        }

        // return $this->sendResponse(null, 'No active plan is linked, but previous purchase was found.');
    }


    // public function testtt(Request $request)
    // {
    //     $subscriptionDetail = $this->getAndroidSubscriptionDetails('com.fitpro360', 'com.fitpro360.workoutonly', 'djmnijbelhpdohojchogdfgh.AO-J1OywCtOpzvYEc3-nYDXjJqyNyPbzvsRh8tGgC-kZl92iKJw1UNWknFElLiE77u4QTN9AefsvkTk4LJ6l30E9ZaGVJ3o1Vg');


    //     $data = DB::table('ft_subscription_webhook')->where('id', '>=', 1)->get();
    //     // ->where('type', 'android')
    //     foreach ($data as $item) {

    //         $webhookArray = json_decode($item->data);

    //         $base64Decode = base64_decode($webhookArray->message->data);
    //         $base64Decode = json_decode($base64Decode, true);
    //         $base64Decode['iddd'] = $item->id;
    //     }
    // }
}
