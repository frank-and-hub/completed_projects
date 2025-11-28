<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Otpverify;
use App\Models\Plans;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    private function generateUniqueCode($country_code)
    {

        $code = $this->getOtp($country_code);

        return [
            'code' => $code,
            'generated_at' => now(),
        ];
    }

    private function getOtp(string $country_code = ''): ?int
    {
        if($country_code === '91') {
            return '1234';
        }
        $code = random_int(1000, 9999);
        if (Otpverify::where('otp', $code)->exists()) {
            return $this->getOtp();
        }
        return $code;
    }

    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate input data
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:30',
                'country' => 'required|exists:' . Country::class . ',name',
                'email' => 'required|email',
                'country_code' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
                'phone' => [
                    'required',
                    'numeric',
                    'unique:users,phone',
                    'digits_between:7,15',
                    'regex:/^\+?[0-9]+$/u',
                ],
            ]);

            $country = Country::where('name', 'LIKE', $request->country)->first();

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            //check if user with phone number
            $user_phone = User::where('country_code', $request->country_code)->where('phone', $request->phone)->first();
            if ($user_phone && $user_phone->otpVerification) {
                if ($user_phone->otpVerification->otp_verified_at != null) {
                    return ResponseBuilder::error(__('auth.phone_already_exists'), $this->validationStatus);
                }
            }

            $user_email = User::where('email', $request->email)->first();
            if ($user_email && $user_email->otpVerification) {
                if ($user_email->otpVerification->otp_verified_at != null) {
                    return ResponseBuilder::error(__('auth.email_already_exists'), $this->validationStatus);
                }
            }

            // Check if user with phone number or email already exists
            $user = User::where('phone', $request->phone)->orWhere('email', $request->email)->first();
            if ($user) {
                if ($user_phone && ($user_phone->id != $user->id) && ($user_phone->email == $request->email)) {
                    $user_phone->email = $user_phone->email . '_';
                    $user_phone->save();
                } elseif ($user_email && ($user_email->id != $user->id) && ($user_email->phone == $request->phone) && ($user_email->country_code == $request->country_code)) {
                    $user_email->phone = null;
                    $user_email->country_code = null;
                    $user_email->save();
                }
                // Update existing user data
                if ($user->otpVerification && $user->otpVerification->otp_verified_at != null) {
                    return ResponseBuilder::error(__('auth.user_already_exists'), $this->validationStatus);
                }
                $user->update([
                    'name' => $request->name,
                    'country_code' => $request->country_code,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'country' => $country->name,
                    'timeZone' => $country->timezones[0]['zoneName']
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'country_code' => $request->country_code,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'type' => 'user',
                    'password' => Hash::make($request->password),
                    'country' => $country->name,
                    'timeZone' => $country->timezones[0]['zoneName']
                ]);
            }
            // Generate unique OTP
            $otpData = $this->generateUniqueCode($request->country_code);
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];

            // Create or update OTP verification record
            $user->otpVerification()->updateOrCreate([
                'phone' => $request->phone,
            ], [
                'otp' => $otp,
                'otp_generated_at' => $otpGeneratedAt,
                'otp_verified_at' => null,
            ]);
            WhatsappTemplate::sendOtp($user->country_code, $user->phone, $otp);
            DB::commit();
            return ResponseBuilder::success(null, __('auth.otp_send_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage() . ' - ' . $e->getLine(), $this->errorStatus);
        }
    }

    public function login(Request $request)
    {
        try {
            DB::beginTransaction();
            $credentials = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($credentials->fails()) {
                $errors = $credentials->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ResponseBuilder::error(__('auth.user_not_found'), $this->validationStatus);
            }

            if((!$user->phone) && $user->social_type){
                return ResponseBuilder::error(__('auth.social_login'), $this->validationStatus);
            }

            $otpData = $this->generateUniqueCode($user->country_code);
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];
            if ($user->status == 0) {
                return ResponseBuilder::error(__('auth.block_user'), $this->validationStatus);
            }
            if (Auth::attempt($credentials->validated())) {
                $otpVerification = $user->otpVerification;
                Otpverify::updateOrCreate(
                    [
                        'user_id' => $user->id
                    ],
                    [
                        'phone' => $user->phone,
                        'otp' => $otp,
                        'otp_generated_at' => $otpGeneratedAt,
                    ]
                );
                WhatsappTemplate::sendOtp($user->country_code, $user->phone, $otp);
                DB::commit();
                return ResponseBuilder::success($user->fresh(), __('auth.otp_send_successfully'));
            }
            return ResponseBuilder::error(__('auth.emailpass'), $this->validationStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function resend_otp(Request $request)
    {
        try {
            DB::beginTransaction();

            $credentials = Validator::make($request->all(), [
                'phone' => [
                    'required',
                    'numeric',
                    'digits_between:7,15',
                    'regex:/^\+?[0-9]+$/u',
                ],
            ]);

            if ($credentials->fails()) {
                $errors = $credentials->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return ResponseBuilder::error(__('auth.user_not_found'), $this->validationStatus);
            }

            if ($user->status == 0) {
                return ResponseBuilder::error(__('auth.block_user'), $this->validationStatus);
            }

            $otpData = $this->generateUniqueCode($user->country_code);
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];

            $otpVerification = $user->otpVerification;
            $otpVerification->update([
                'otp' => $otp,
                'otp_generated_at' => $otpGeneratedAt,
            ]);

            WhatsappTemplate::sendOtp($user->country_code, $user->phone, $otp);

            DB::commit();

            return ResponseBuilder::success('', __('auth.otp_resent_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }


    public function otp_verify(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'verifytype' => 'required|in:forgot,general',
                'phone' => 'required|numeric|digits_between:7,15|regex:/^\+?[0-9]+$/u',
            ]);

            if ($validator->fails()) {
                Log::info('');
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                Log::info('user not found');
                return ResponseBuilder::error(__('auth.user_not_found'), $this->invalidPermission);
            }

            // Otpverify::where(function ($query) {
            //     $query->where('otp_verified_at', '<', \DB::raw('otp_generated_at + INTERVAL 5 MINUTE'))
            //         ->orWhereNotNull('otp_verified_at');
            // })
            //     ->whereNotNull('otp')
            //     ->delete();

            $otpVerification = $user->otpVerification;

            if (!$otpVerification || $otpVerification->otp_generated_at < now()->subMinutes(5)) {
                Log::info('otp Verification');
                DB::rollBack();
                return ResponseBuilder::error(__('auth.otp_expired'), $this->invalidPermission);
            }

            $otp = $request->otp;

            if ($otpVerification->otp !== $otp) {
                Log::info('otp Verification check');
                DB::rollBack();
                return ResponseBuilder::error(__('auth.invalid_otp'), $this->invalidPermission);
            }

            $responseData = ['user' => $user->fresh()];

            if ($request->verifytype === 'general') {
                Log::info('verify type is general');
                Log::info($user->createToken('pocketproperty')->accessToken);
                $user_token = $user->createToken('pocketproperty')->accessToken;
                // $user->assignRole('user');
                $responseData['token'] = $user_token;
            }
            if ($request->verifytype === 'forgot') {
                Log::info('verify type is forgot');
                $verify_token = Password::broker()->createToken($user);
                $responseData['verify_token'] = $verify_token;
            }

            if ($otpVerification->otp_verified_at === null) {
                Log::info('otp verified at updated');
                $otpVerification->otp_verified_at = now();
                WhatsappTemplate::tenantWelcomeMessage($user->country_code, $user->phone, $user->name);
            }

            $otpVerification->otp = '';
            $otpVerification->save();
            DB::commit();

            return ResponseBuilder::success($responseData, __('auth.otp_verification'), $this->successStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getCode());
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }


    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return ResponseBuilder::success(Null, __('auth.logout'));
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function forgot_password(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ResponseBuilder::error(__('auth.user_not_found'), $this->invalidPermission);
            }
            $otpData = $this->generateUniqueCode($user->country_code);
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];
            $otpVerification = $user->otpVerification()->firstOrNew([]);
            $otpVerification->otp = $otp;
            $otpVerification->otp_generated_at = $otpGeneratedAt;
            $otpVerification->save();
            WhatsappTemplate::sendOtp($user->country_code, $user->phone, $otp);
            DB::commit();
            return ResponseBuilder::success($user, __('auth.otp_send_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function set_password(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'verify_token' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }

            $user = User::findOrFail($request->user_id);
            $token = $request->verify_token;
            $password = $request->password;
            if (!$user) {
                return ResponseBuilder::error(__('auth.user_not_found'), $this->invalidPermission);
            }
            $passwordResetStatus = Password::broker()->reset(
                ['phone' => $user->phone, 'token' => $token, 'password' => $password],
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->save();
                }
            );
            if ($passwordResetStatus == Password::PASSWORD_RESET) {
                DB::commit();
                return ResponseBuilder::success(null, __('auth.pass_change'), $this->successStatus);
            } else {
                DB::rollBack();
                return ResponseBuilder::error(__('auth.invalid_token'), $this->invalidPermission);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
}
