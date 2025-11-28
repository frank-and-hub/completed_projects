<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AgencyRegister;
use App\Models\Country;
use App\Models\PrivateLandlord;
use App\Models\PropertyContact;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    private function generateUniqueCode($type)
    {

        $code = $this->getOtp($type);

        return [
            'code' => $code,
            'generated_at' => now(),
        ];
    }

    private function getOtp(string $type = ''): ?int
    {
        $code = random_int(1000, 9999);
        $column = $type === 'email' ? 'email_otp' : 'phone_otp';
        if (PrivateLandlord::where($column, $code)->exists()) {
            return $this->getOtp($type);
        }
        return $code;
    }

    public function login()
    {
        $title = 'Admin';
        return view('auth.login', compact('title'));
    }

    public function sub_login($type_url)
    {
        if (!in_array($type_url, ['privatelandlord', 'agency', 'agent'])) {
            return redirect()->back();
        }
        $title = ucwords($type_url); //'Landlord';
        return view('auth.subAdminLogin', compact('title', 'type_url'));
    }

    public function loginProcess(Request $request)
    {
        try {
            $credentials = Validator::make($request->all(), [
                'password' => 'required|min:5',
                'email' => 'required|email|exists:' . Admin::class . ',email'
            ]);

            if ($credentials->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $credentials->errors(),
                ], 422);
            }

            $admin = Admin::where('email', $request->email)->first();

            if (!$admin->hasRole('admin')) {
                Log::info('admin user not found');
                return response()->json(['status' => 'error', 'message' => 'Login details are not valid']);
            }

            if (Auth::guard('admin')->attempt($credentials->validated())) {
                Log::info('credentials are not matched with user');
                $user = Auth::guard('admin')->user();
                return response()->json(['status' => 'success', 'message' => 'Login Successfully']);
            }
            return response()->json(['status' => 'error', 'message' => 'Login details are not valid']);
        } catch (QueryException $e) {
            Log::error($e);
            return response()->json(['status' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error', 'msg' => 'Something Went Wrong: ' . $e->getMessage()]);
        }
    }

    public function sub_loginprocess(Request $request, $type)
    {
        try {
            $credentials = Validator::make($request->all(), [
                'password' => 'required|min:5',
                'email' => 'required|email'
            ]);

            if ($credentials->fails()) {
                return response()->json([
                    'status' => 'serverside_error',
                    'msg' => 'Validation failed',
                    'errors' => $credentials->errors(),
                ], 422);
            }

            $admin = Admin::where('email', $request->email)->first();
            $role = $admin?->getRoleNames()->first() ?: '';

            if (!$admin || ($role == 'admin')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ]);
            }

            if ($type != $role) {
                return response()->json([
                    'status' => 'error',
                    'message' => ucwords($type) . ' user not found'
                ]);
            }

            if ($role == 'privatelandlord') {
                if (
                    !$admin->privateLandlord ||
                    is_null($admin->privateLandlord->phone_otp_verified_at) ||
                    is_null($admin->privateLandlord->email_otp_verified_at)
                ) {

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Your account is not verified, please complete your registration process'
                    ]);
                }
            } elseif ($role == 'agency') {
                // if($admin->request_type == "pending"){
                //     return response()->json([
                //         'status' => 'error',
                //         'message' => 'Your account process is on pending.'
                //     ]);
                // }elseif($admin->request_type == "cancelled"){
                //     return response()->json([
                //         'status' => 'error',
                //         'message' => 'Your registration process is cancelled.'
                //     ]);
                // } else {
                // }
                if (!$admin->status) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Your account is blocked by admin.'
                    ]);
                }
            } else {
                // return response()->json([
                //     'status' => 'error',
                //     'message' => 'Comming soon.'
                // ]);
            }

            if (!$admin->status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is blocked by admin.'
                ]);
            }

            if (Auth::guard('admin')->attempt($credentials->validated())) {
                $user = Auth::guard('admin')->user();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login Successfully'
                ]);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Login details are not valid'
            ]);
        } catch (QueryException $e) {
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Database error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'status' => 'error',
                'msg' => 'Something Went Wrong: ' . $e->getMessage()
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        switch ($role) {
            case 'admin':
                $route = "admin";
                break;
            case 'agent':
                $route = "agent/login";
                break;
            case 'agency':
                $route = "agency/login";
                break;
            case 'privatelandlord':
                $route = "privatelandlord/login";
                break;
            default:
                $route = "/";
                break;
        }

        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($route);
    }

    public function privatelandlord_signup(Request $request)
    {
        $credentials = Validator::make($request->all(), [
            'email' => 'required|email|unique:' . Admin::class . ',email',
            'name' => 'required',
            'country_code' => 'required',
            'country' => 'required|exists:' . Country::class . ',name',
            'phone' => [
                'required',
                'numeric',
                'digits_between:7,15',
                'regex:/^\+?[0-9]+$/u',
            ],
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'image' => 'required|string'
        ]);

        if ($credentials->fails()) {
            $errors = $credentials->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        try {
            $country = Country::where('name', 'LIKE', $request->country)->first();

            DB::beginTransaction();
            $name = $request->name;
            $email = $request->email;
            $country_code = $request->country_code;
            $phone = $request->phone;
            $password = $request->password;
            $landlord = Admin::where(function ($q) use ($email, $phone) {
                $q->where('email', $email)
                    ->orWhere('phone', $phone);
            })
                ->first();
            if ($landlord) {
                if ($landlord->privateLandlord->phone_otp_verified_at != null && $landlord->PrivateLandlord->email_otp_verified_at != null) {

                    if ($landlord->phone == $phone) {
                        return ResponseBuilder::error(__('auth.phone_already_exists'), $this->validationStatus);
                    } elseif ($landlord->phone == $email) {
                        return ResponseBuilder::error(__('auth.email_already_exists'), $this->validationStatus);
                    }
                    return ResponseBuilder::error(__('auth.user_already_exists'), $this->validationStatus);
                }
                $landlord->update([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'password_text' => $password
                ]);
            } else {
                $newAdminDetails = [
                    'name' => $name,
                    'email' => $email,
                    'dial_code' => $country_code,
                    'phone' => $phone,
                    'password' => Hash::make($password),
                    'password_text' => $password,
                    'request_type' => 'accepted',
                    'country' => $country->name,
                    'timeZone' => $country->timezones[0]['zoneName']
                ];
                $landlord = Admin::create($newAdminDetails);
            }

            if ($request->hasFile('image')) {
                $path = $this->__imageSave($request, 'image', 'signup');
                $landlord->media()->create([
                    'type' => 'image',
                    'path' => $path
                ]);
            } elseif ($request->image) {
                $extracted = str_contains($request->image, '/storage/images/') ? str_replace('/storage/images/', 'profile-image/', $request->image) : $request->image;
                // if (Storage::exists($extracted)) {
                $landlord->media()->create([
                    'type' => 'image',
                    'path' => $extracted
                ]);
                // }
            }

            // Generate unique OTP
            $otpData = $this->generateUniqueCode('phone');
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];

            // Create or update OTP verification record
            $landlord->privateLandlord()->updateOrCreate([
                'admin_id' => $landlord->id,
            ], [
                'phone_otp' => $otp,
                'phone_otp_generated_at' => $otpGeneratedAt,
            ]);
            WhatsappTemplate::sendOtp($country_code, $phone, $otp);
            DB::commit();
            return ResponseBuilder::success(null, __('auth.otp_send_successfully'));
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }


    public function privatelandlord_verify(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'verifytype' => 'required|in:phone,email',
            'email' => 'required_if:verifytype,email|email',
            'phone' => 'required_if:verifytype,phone|numeric|digits_between:7,15|regex:/^\+?[0-9]+$/u',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->validationStatus);
        }

        // Determine the verification type (phone or email) and get the landlord record
        $landlord = Admin::where($request->verifytype, $request->{$request->verifytype})->first();
        if (!$landlord) {
            return ResponseBuilder::error(__('auth.user_not_found'), $this->invalidPermission);
        }

        $otpVerification = $landlord->privateLandlord;
        if (!$otpVerification) {
            Log::info('for otpVerification privateLandlord not found');
            return ResponseBuilder::error(__('auth.otp_expired'), $this->invalidPermission);
        }

        // Check OTP expiry and verification
        $otpTypeField = "{$request->verifytype}_otp";
        $otpGeneratedAtField = "{$request->verifytype}_otp_generated_at";
        $otpVerifiedAtField = "{$request->verifytype}_otp_verified_at";

        if ($otpVerification->$otpGeneratedAtField < now()->subMinutes(5)) {
            Log::info('for otpVerification otpGeneratedAtField is expired');
            return ResponseBuilder::error(__('auth.otp_expired'), $this->invalidPermission);
        }

        if ($otpVerification->$otpTypeField !== $request->otp) {
            Log::info('otp not matched');
            return ResponseBuilder::error(__('auth.invalid_otp'), $this->invalidPermission);
        }

        // Mark OTP as verified if not already
        if (is_null($otpVerification->$otpVerifiedAtField)) {
            $otpVerification->$otpVerifiedAtField = now();
        }

        // If verifying via phone, generate a new email OTP
        if ($request->verifytype === 'phone') {
            $otpData = $this->generateUniqueCode('email');
            $newOtp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];

            Helper::sendOtp($landlord, $newOtp);

            // Reset phone OTP and set new email OTP
            $otpVerification->phone_otp = '';
            $otpVerification->email_otp = $newOtp;
            $otpVerification->email_otp_generated_at = $otpGeneratedAt;
            $otpVerification->save();

            $responseData = ['email' => $landlord->email];
            return ResponseBuilder::success($responseData, __('auth.otp_verification'), $this->successStatus);
        }

        // For email verification, reset email OTP
        $otpVerification->email_otp = '';
        $otpVerification->save();
        $landlord->assignRole('privatelandlord');
        WhatsappTemplate::landlordWelcomeMessage($landlord->dial_code, $landlord->phone, $landlord->name);
        return ResponseBuilder::success(null, __('auth.otp_verification'), $this->successStatus);
    }

    public function privatelandlord_resend_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verifytype' => 'required|in:phone,email',
            'email' => 'required_if:verifytype,email|email',
            'phone' => 'required_if:verifytype,phone|numeric|digits_between:7,15|regex:/^\+?[0-9]+$/u',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->validationStatus);
        }

        $landlord = Admin::where($request->verifytype, $request->{$request->verifytype})->first();
        if (!$landlord) {
            return ResponseBuilder::error(__('auth.user_not_found'), $this->invalidPermission);
        }

        $dial_code = $landlord->dial_code;
        $otpData = $this->generateUniqueCode($dial_code);
        $otp = $otpData['code'];
        $otpGeneratedAt = $otpData['generated_at'];
        $otpTypeField = "{$request->verifytype}_otp";
        $otpGeneratedAtField = "{$request->verifytype}_otp_generated_at";
        $landlord->privateLandlord()->updateOrCreate([
            'admin_id' => $landlord->id,
        ], [
            $otpTypeField => $otp,
            $otpGeneratedAtField => $otpGeneratedAt,
        ]);
        if ($request->verifytype == 'phone') {
            WhatsappTemplate::sendOtp($dial_code, $request->phone, $otp);
        } else {
            Helper::sendOtp($landlord, $otp);
        }
        return ResponseBuilder::success(null, __('auth.otp_send_successfully'));
    }

    public function agencySignup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:' . Admin::class . ',email',
            'dial_code' => 'required',
            'phone' => 'required|numeric|digits_between:7,15|regex:/^\+?[0-9]+$/u|unique:admins,phone',
            'f_name' => 'required',
            'l_name' => 'required',
            'id_number' => 'required|unique:agency_register,id_number',
            'business_name' => 'required|unique:agency_register,business_name',
            'registration_number' => 'required|unique:agency_register,registration_number',
            'vat_number' => 'required|unique:agency_register,vat_number',
            'postal_code' => 'required|unique:agency_register,postal_code',
            'type_of_business' => 'required',
            'country' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::error($validator->errors()->first(), $this->validationStatus);
        }

        DB::beginTransaction();

        $path = null;

        try {
            // Check if an admin with the same email or phone already exists
            $existingAdmin = Admin::where('email', $request->email)
                ->orWhere('phone', $request->phone)
                ->first();

            if ($existingAdmin) {
                return ResponseBuilder::error(__('auth.user_already_exists'), $this->validationStatus);
            }

            // Create the new Admin
            $fullName = $request->f_name . ' ' . $request->l_name;
            $agency = Admin::create([
                'name' => $fullName,
                'email' => $request->email,
                'dial_code' => $request->dial_code,
                'password' => Hash::make('12345678'),
                'phone' => $request->phone,
                'request_type' => 'pending',
            ]);

            if ($request->hasFile('image')) {
                $path = $this->__imageSave($request, 'image', 'agency_banner');
            }

            // If admin creation is successful, create Agency Register
            AgencyRegister::create([
                'admin_id' => $agency->id,
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'business_name' => $request->business_name,
                'id_number' => $request->id_number,
                'registration_number' => $request->registration_number,
                'vat_number' => $request->vat_number,
                'street_address' => $request->street_address,
                'street_address_2' => $request->street_address_2,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'type_of_business' => $request->type_of_business,
                'country' => $request->country,
                'message' => $request->message,
                'agency_banner' => $path
            ]);

            $agency->assignRole('agency');
            DB::commit();

            return ResponseBuilder::success(null, 'Your request has been submitted. After verifying your account, you will receive your login details via the registered email.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }


    // forget password
    public function forgot_password(Request $request, $type_url)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:' . Admin::class . ',email',
                'otp' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), 200);
            }
            $admin = Admin::where('email', $request->email)->first();
            if ($admin->hasRole('admin')) {
                return ResponseBuilder::error("Invalid Email", 200);
            }
            if (!$request->otp) {
                $otpData = $this->generateUniqueCode('phone');
                $otp = $otpData['code'];
                $otpGeneratedAt = $otpData['generated_at'];
                WhatsappTemplate::sendOtp($admin->dial_code, $admin->phone, $otp);
                $admin->otpVerification()->updateOrCreate(
                    [
                        'admin_id' => $admin->id,
                    ],
                    [
                        'type' => 'forgotPassword',
                        'otp' => $otp,
                        'expiry_at' => Carbon::now()->addMinutes(10)
                    ]
                );
                return ResponseBuilder::success(null, 'Successfully! Send otp.');
            } else {
                $otp_v = $admin->otpVerification()->first();
                if ($otp_v->otp != $request->otp) {
                    return ResponseBuilder::error("Invalid OTP", 200);
                } else {
                    $data = [
                        'verification' => $admin->email,
                        'otp' => $request->otp
                    ];
                    return ResponseBuilder::success($data, 'Successfully! Correct otp.');
                }
            }
        } else {
            return view('adminsubuser.forgetPassword2', compact('type_url'));
        }
    }

    public function reset_password(Request $request, $type_url)
    {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:' . Admin::class . ',email',
                'password' => 'required|string|min:6',
                'c_password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return ResponseBuilder::error($validator->errors()->first(), 200);
            }

            if (!$request->otp) {
                return response()->json([
                    'status' => 2,
                    'message' => 'Something Went Wrong',
                ]);
            }

            $admin = Admin::where('email', $request->email)->first();
            if ($admin && (!$admin->hasRole('admin'))) {

                $pass = Hash::make($request->password);
                $admin->update([
                    'password' => $pass,
                    'password_text' => $request->password,
                ]);
                return ResponseBuilder::success([], 'Successfully! Password update');
            } else {
                return ResponseBuilder::error('User not found', $this->errorStatus);
            }
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
}
