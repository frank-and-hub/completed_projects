<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Plans;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PayFast\PayFastPayment;

class paymentController extends Controller
{
    private $passphrase;
    private $merchant_id;
    private $merchant_key;
    private $payfast_mode;
    private $payfast_url;

    public function __construct()
    {
        $this->payfast_mode = config('services.payfast.payfast_mode');
        $this->payfast_url = config('services.payfast.payfast_url');
        $this->merchant_key = config('services.payfast.merchant_key');
        $this->merchant_id = config('services.payfast.merchant_id');
        $this->passphrase = config('services.payfast.passphrase');
    }

    function generateSignature($data, $passPhrase = null)
    {
        // Create parameter string
        $pfOutput = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        // Remove last ampersand
        $getString = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    /**
     * Subscription
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function subscription_old(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseBuilder::error('Please log in first', $this->validationStatus);
        }

        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        if ($user->subscription == 1) {
            return ResponseBuilder::error('You already have an ongoing subscription', $this->validationStatus);
        }
        $existingSubscription = $user->user_subscription->where('is_active', 1)->where('total_request', 5)->first();
        if ($user->subscription == 0 && $existingSubscription) {
            $existingSubscription->update(['is_active' => 0]);
        }

        $started_at = Carbon::now();
        $expired_at = $started_at->copy()->addMonth();

        $data = [
            'user_id' => $user->id,
            'subscription_id' => $request->subscription_id,
            'amount' => $request->amount,
            'status' => 'ongoing',
            'started_at' => $started_at,
            'expired_at' => $expired_at
        ];

        DB::beginTransaction();

        try {
            $insert = UserSubscription::create($data);
            $user->update(['subscription' => 1]);
            $receiptData = [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'plan_name' => $insert->plan->plan_name,
                'receipt_no' => $insert->id,
                'receipt_amount' => $insert->amount,
                'receipt_date' => Carbon::parse($insert->created_at, 'Africa/Johannesburg')->format('d M y, g:i A'),
            ];
            Helper::sendReceiptMail($receiptData);
            DB::commit();
            return ResponseBuilder::success(null, 'Subscription successfully created', $this->successStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error('Subscription creation failed: ' . $e->getMessage(), $this->errorStatus);
        }
    }

    public function check_subscription(Request $request)
    {
        $user = Auth::user();
        $user->update_plans();
        $existingSubscription = $user->user_subscription->where('is_active', 1)->where('total_request', 5)->first();
        if ($existingSubscription) {
            $existingSubscription->update(['is_active' => 0]);
            $user->update(['subscription' => 0]);
        }
        if ($user->subscription == 1) {
            return ResponseBuilder::error('You already have an ongoing subscription', $this->validationStatus);
        } else {
            return ResponseBuilder::success(null, 'You do not have an ongoing subscription', $this->successStatus);
        }
    }

    // payfast payment form
    public function subscription(Request $request)
    {
        Log::info('Payfast subscription request: ' . json_encode($request->all()));
        $user = Auth::user();
        if (!$user) {
            return ResponseBuilder::error('Please log in first', $this->validationStatus);
        }

        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:plans,id',
            // 'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }
        if ($user->subscription == 1) {
            return ResponseBuilder::error('You already have an ongoing subscription', $this->validationStatus);
        }
        $subscription = Plans::find($request->subscription_id);

        try {
            $form_data = [
                'merchant_id' => $this->merchant_id,
                'merchant_key' => $this->merchant_key,
                'return_url' => route('payfast_success', [
                    'subscription_id' => $request->subscription_id,
                    'user_id' => $user->id,
                ]),
                'cancel_url' => route('payfast_cancel'),
                'notify_url' => route('payfast_notify', [
                    'subscription_id' => $request->subscription_id,
                    'user_id' => $user->id,
                ]),
                'name_first' => $user->name,
                'name_last' => $user->name,
                'email_address' => $user->email,
                'm_payment_id' => $user->id . uniqid(),
                'amount' => number_format(sprintf('%.2f', $subscription->amount), 2, '.', ''),
                'item_name' => $subscription->plan_name,
            ];
            $signature = $this->generateSignature($form_data, $this->passphrase);
            $form_data['signature'] = $signature;
            return ResponseBuilder::success($form_data, '');
        } catch (\Exception $e) {
            return ResponseBuilder::error('Subscription creation failed: ' . $e->getMessage(), $this->errorStatus);
        }
    }

    public function payfast_notify(Request $request, $subscription_id, $user_id)
    {
        $pfData = $_POST;
        Log::info('Payfast notify: ' . json_encode($pfData));
        $pfParamString = '';
        // Strip any slashes in data
        foreach ($pfData as $key => $val) {
            $pfData[$key] = stripslashes($val);
            if ($key !== 'signature') {
                $pfParamString .= $key . '=' . urlencode($val) . '&';
            }
        }
        // Remove the last '&' from the parameter string
        $pfParamString = substr($pfParamString, 0, -1);

        if ($this->pfValidSignature($pfData, $pfParamString) && $this->pfValidIP()) {
            Log::info('Payfast notify: Signature and IP are valid');
            // DB::beginTransaction();
            try {
                $payment_status = $pfData['payment_status'];
                $m_payment_id = $pfData['m_payment_id'];
                $pf_payment_id = $pfData['pf_payment_id'];
                $amount_gross = $pfData['amount_gross'];
                $amount_fee = $pfData['amount_fee'];
                $amount_net = $pfData['amount_net'];

                if ($payment_status == 'COMPLETE') {
                    Log::info('Payfast notify: Payment status is complete');

                    $subscription = Plans::find($subscription_id);
                    if (!$subscription) {
                        throw new \Exception('Subscription not found');
                    }

                    $user = User::find($user_id);
                    if (!$user) {
                        throw new \Exception('User not found');
                    }
                    $existingSubscription = $user->user_subscription->where('is_active', 1)->where('total_request', 5)->first();
                    if ($user->subscription == 0 && $existingSubscription) {
                        $existingSubscription->update(['is_active' => 0]);
                    }

                    $started_at = Carbon::now();
                    $expired_at = $started_at->copy()->addMonth();

                    $data = [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription_id,
                        'amount' => $amount_gross,
                        'amount_fee' => $amount_fee,
                        'amount_net' => $amount_net,
                        'status' => 'ongoing',
                        'pf_payment_id' => $pf_payment_id,
                        'started_at' => $started_at,
                        'expired_at' => $expired_at,
                    ];

                    $insert = UserSubscription::create($data);
                    $user->update(['subscription' => 1]);

                    $receiptData = [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'plan_name' => $insert->plan->plan_name,
                        'receipt_no' => $insert->pf_payment_id,
                        'amount' => $insert->amount,
                        'amount_net' => $insert->amount_net,
                        'amount_fee' => $insert->amount_fee,
                        'receipt_date' => Carbon::parse($insert->created_at, 'Africa/Johannesburg')->format('y-m-d'),
                    ];
                    Helper::sendReceiptMail($receiptData);
                }
                Log::info('Payfast notify: Subscription successfully created');
                // DB::commit();
                return ResponseBuilder::success(null, 'Subscription successfully created', $this->successStatus);
            } catch (\Exception $e) {
                // DB::rollBack();
                return ResponseBuilder::error('Subscription creation failed: ' . $e->getMessage(), $this->errorStatus);
            }
        }
    }

    public function pfValidSignature($pfData, $pfParamString)
    {
        $pfPassphrase = $this->passphrase;
        $pfSignature = $pfData['signature'];
        return $pfSignature === md5($pfParamString . '&passphrase=' . $pfPassphrase);
    }

    public function pfValidIP()
    {
        $validHosts = [
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        ];
        $validIps = [];
        foreach ($validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);

            Log::debug('Payfast notify: Valid IPs: ' . json_encode($ips));

            if ($ips !== false) {
                $validIps = array_merge($validIps, $ips);
            }
        }

        $ip = $this->getIp();
        return in_array($ip, $validIps);
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            log::debug('HTTP_CLIENT_IP---' . $ip_address);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($iplist as $ip) {
                    $ip_address = $ip;
                    log::debug('HTTP_X_FORWARDED_FOR60---' . $ip_address);
                }
            } else {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
                log::debug('HTTP_X_FORWARDED_FOR else part63---' . $ip_address);
                return $ip_address;
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED'];
            log::debug('HTTP_X_FORWARDED_FOR else part70---' . $ip_address);
        } elseif (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ip_address = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            log::debug('HTTP_X_FORWARDED_FOR else part73---' . $ip_address);
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED_FOR'];
            log::debug('HTTP_X_FORWARDED_FOR else part76---' . $ip_address);
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ip_address = $_SERVER['HTTP_FORWARDED'];
            log::debug('HTTP_X_FORWARDED_FOR else part79---' . $ip_address);
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
            log::debug('HTTP_X_FORWARDED_FOR else part82---' . $ip_address);
        }
        return $ip_address;
    }

    // public function payfast_success(Request $request, $subscription_id, $user_id)
    // {
    //     $redirectUrl = url('/') . '?payment=success';
    //     return redirect($redirectUrl);
    // }

    public function payfast_success(Request $request, $subscription_id, $user_id)
    {
        try {
            DB::beginTransaction();

            $subscription = Plans::find($subscription_id);
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            $user = User::find($user_id);
            if (!$user) {
                throw new \Exception('User not found');
            }

            $started_at = Carbon::now();
            $expired_at = $started_at->copy()->addMonth();

            $data = [
                'user_id' => $user->id,
                'subscription_id' => $subscription_id,
                'amount' => $subscription->amount,
                'amount_fee' => 0.00,
                'amount_net' => 0.00,
                'status' => 'ongoing',
                'pf_payment_id' => '00001',
                'started_at' => $started_at,
                'expired_at' => $expired_at,
            ];

            $insert = UserSubscription::create($data);
            $user->update(['subscription' => 1]);

            $receiptData = [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'plan_name' => $insert->plan->plan_name,
                'receipt_no' => $insert->pf_payment_id,
                'amount' => $insert->amount,
                'amount_net' => $insert->amount_net,
                'amount_fee' => $insert->amount_fee,
                'receipt_date' => Carbon::parse($insert->created_at, 'Africa/Johannesburg')->format('ymd'),
            ];
            Helper::sendReceiptMail($receiptData);

            DB::commit();
            $redirectUrl = url('/') . '?payment=success';
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            DB::rollBack();
            // Redirect back to the previous URL
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function payfast_cancel()
    {
        // return redirect()->back();
        $redirectUrl = url('/');
        return redirect($redirectUrl);
    }

    public function free_plan(Request $request, $id)
    {
        $auth = Auth::user();
        $plan = Plans::whereStatus(1)->find($id);

        if ($plan && ($plan->amount == 0)) {
            $started_at = Carbon::now();
            $expired_at = $started_at->copy()->addMonth(110);
            $data = [
                'user_id' => $auth->id,
                'subscription_id' => $plan->id,
                'amount' => 0,
                'amount_fee' => 0,
                'amount_net' => 0,
                'status' => 'ongoing',
                'started_at' => $started_at,
                'expired_at' => $expired_at,
            ];
            DB::beginTransaction();
            $insert = UserSubscription::create($data);
            User::where('id', $auth->id)->update([
                'subscription' => 1
            ]);
            DB::commit();
            return ResponseBuilder::success(null, 'Successfully! subscribed', $this->successStatus);
        } else {
            return ResponseBuilder::error('Free Plan does not exists', $this->validationStatus);
        }
    }
}
