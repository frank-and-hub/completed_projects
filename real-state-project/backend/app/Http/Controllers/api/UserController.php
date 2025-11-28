<?php

namespace App\Http\Controllers\api;

use App\Helpers\Helper;
use App\Helpers\ResponseBuilder;
use App\Helpers\WhatsappTemplate;
use App\Http\Controllers\Controller;
use App\Http\Resources\TranscationResource;
use App\Http\Resources\UserEmploymentResource;
use App\Http\Resources\UserResource;
use App\Models\Country;
use App\Models\Otpverify;
use App\Models\User;
use App\Models\UserEmployment;
use App\Models\UserScheduleTime;
use App\Models\UserSubscription;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Class UserController
     *
     * This controller handles user-related operations such as profile management,
     * OTP verification, password changes, message scheduling, and transaction history.
     *
     * Methods:
     * - generateUniqueCode(): Generates a unique OTP code.
     * - profile(): Retrieves the authenticated user's profile and subscription details.
     * - profile_update(Request $request): Updates the user's profile information, including name, phone, and image.
     * - profile_resendOtp(Request $request): Resends an OTP for verification based on the provided verification type.
     * - profile_verify(Request $request): Verifies the OTP for phone or password changes.
     * - change_password(Request $request): Initiates a password change process by sending an OTP for verification.
     * - set_message_schedule_time(Request $request): Sets the user's message schedule time based on subscription.
     * - getCurrentTimeInUTC($timezone): Gets the current time in UTC for a given timezone.
     * - convertToUTC($time, $timezone): Converts a given time to UTC based on the provided timezone.
     * - message_alert(Request $request): Updates the user's message alert preference.
     * - transaction_history(Request $request): Retrieves the user's transaction history for subscriptions.
     *
     * Dependencies:
     * - Uses various models such as User, Otpverify, UserSubscription, and UserScheduleTime.
     * - Utilizes helper classes like Helper, ResponseBuilder, and WhatsappTemplate.
     * - Relies on Laravel's built-in features like Auth, Validator, and DB transactions.
     */

    public function generateUniqueCode()
    {
        $code = random_int(1000, 9999);
        if (Otpverify::where('otp', $code)->exists()) {
            return $this->generateUniqueCode();
        }

        return [
            'code' => $code,
            'generated_at' => now(),
        ];
    }
    public function profile()
    {
        $user = Auth::user();
        $user_subscription = $user->user_subscription()->where('is_active', 1)->where('status', UserSubscription::STATUS_ONGOING)->first();
        $schedule_type = $user?->scheduletime->schedule_type ?? null;
        if ($user_subscription) {
            $user['subscription_type'] = $user_subscription->plan->plan_name;
            $user['total_request'] = $user_subscription->total_request;
            $user['schedule_type'] = $schedule_type;
        }
        $data = new UserResource($user);
        return ResponseBuilder::success($data, '');
    }

    public function profile_update(Request $request)
    {
        $user = Auth::user();
        $validationArray = [
            'name' => 'required|min:3|max:30',
            'country' => 'required|exists:' . Country::class . ',name',
            'country_code' => 'required',
            'phone' => [
                'required',
                'numeric',
                'digits_between:7,15',
                'regex:/^\+?[0-9]+$/u',
                'unique:users,phone,' . Auth::id(),
            ],
            'email' => 'required|email|unique:' . User::class . ',email,' . Auth::id(),
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emplyee_type' => 'required|in:employed,contract,self_employed,student,retired,unemployed',
            'live_with' => 'required|numeric|digits_between:1,20',
        ];

        if (!$user->country) {
            $validationArray['country'] = 'required|exists:' . Country::class . ',name';
        }

        $validator = Validator::make($request->all(), $validationArray);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        DB::beginTransaction();

        try {
            // Get the authenticated user

            // Variables to track changes
            $phoneChanged = ($request->country_code . $request->phone) !== ($user->country_code . $user->phone);
            $phoneNotVerified = !$user->otpVerification || !$user->otpVerification->otp_verified_at;
            // Check if only name and/or image are being updated
            if (!$phoneChanged && !$phoneNotVerified) {
                // Handle image upload if provided
                $imageFileName = $user->image;
                if ($request->hasFile('image')) {
                    $imageFileName = $this->__imageSave($request, 'image', 'users-image');
                    if ($user->image) {
                        Storage::delete($user->image);
                    }
                }

                // Update user details
                $user->name = $request->name;
                $user->image = $imageFileName;
                $user->country = $request->country;

                if (!$user->country) {
                    $country = Country::where('name', $request->country)->first();
                    $user->country = $country->name;
                    $user->timeZone = $country->timezones[0]['zoneName'];
                }

                $user->save();
                $data = [
                    'type' => 'update',
                ];

                UserEmployment::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'emplyee_type' => $request->emplyee_type,
                        'live_with' => $request->live_with,
                    ]
                );

                DB::commit();
                return ResponseBuilder::success($data, __('auth.profile_update'), $this->successStatus);
            }

            // Check if the phone number or password needs to be verified
            if ($phoneChanged || $phoneNotVerified) {
                // Generate OTP and update OTP verification record
                $otpData = $this->generateUniqueCode();
                $otp = $otpData['code'];
                $otpGeneratedAt = $otpData['generated_at'];
                $otpVerification = $user->otpVerification()->firstOrNew([]);
                $otpVerification->otp = $otp;
                $otpVerification->phone = $request->phone;
                $otpVerification->otp_generated_at = $otpGeneratedAt;
                $otpVerification->save();

                // Determine verifytype based on the changes
                if ($phoneChanged || $phoneNotVerified) {
                    $verifytype = 'onlyphone';
                }

                // Send OTP for verification
                $data = [
                    'type' => 'verify',
                    'verifytype' => $verifytype,
                    'email' => $user->email,
                ];

                // Helper::sendOtp($user, $otp);
                WhatsappTemplate::sendOtp($request->country_code, $request->phone, $otp);

                DB::commit();
                return ResponseBuilder::success($data, __('auth.otp_send_successfully'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function profile_resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verifytype' => 'required',
        ]);
        $validator->sometimes(['phone', 'country_code'], 'required', function ($input) {
            return $input->verifytype === 'onlyphone';
        });

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }
        try {
            DB::beginTransaction();

            $user = Auth::user();

            if ($user->status == 0) {
                return ResponseBuilder::error(__('auth.block_user'), $this->validationStatus);
            }

            $otpData = $this->generateUniqueCode();
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];

            $otpVerification = $user->otpVerification;
            $otpVerification->otp_verified_at ??= now();
            $otpVerification->otp = $otp;
            $otpVerification->otp_generated_at = $otpGeneratedAt;
            $otpVerification->save();
            if ($request->verifytype == 'onlyphone') {
                WhatsappTemplate::sendOtp($request->country_code, $request->phone, $otp);
            } else {
                Helper::sendOtp($user, $otp);
            }

            DB::commit();

            return ResponseBuilder::success('', __('auth.otp_resent_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
    public function profile_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'verifytype' => 'required',
        ]);
        $validator->sometimes('phone', 'required', function ($input) {
            return $input->verifytype === 'onlyphone';
        });
        $validator->sometimes('new_password', 'required', function ($input) {
            return $input->verifytype === 'onlypass';
        });

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        try {
            DB::beginTransaction();
            $user = User::findOrFail(Auth::user()->id);

            $otpVerification = $user->otpVerification;

            if (!$otpVerification || $otpVerification->otp_generated_at < now()->subMinutes(5)) {
                DB::rollBack();
                $otpVerification->delete();
                return ResponseBuilder::error(__('auth.otp_expired'), $this->invalidPermission);
            }

            $otp = $request->otp;

            if ($otpVerification->otp !== $otp) {
                DB::rollBack();
                return ResponseBuilder::error(__('auth.invalid_otp'), $this->invalidPermission);
            }

            $otpVerification->otp = '';
            $otpVerification->otp_verified_at ??= now();
            $otpVerification->save();
            $response = __('auth.otp_verification');
            if ($request->verifytype == 'onlyphone') {
                $imageFileName = $user->image;
                if ($request->hasFile('image')) {
                    $imageFileName = $this->__imageSave($request, 'image', 'users-image');
                    if ($user->image) {
                        Storage::delete($user->image);
                    }
                }
                $user->name = $request->name;
                $user->image = $imageFileName;
                $user->phone = $request->phone;
                $user->country = $request->country;
                $user->country_code = $request->country_code;
                $user->save();

                UserEmployment::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'emplyee_type' => $request->emplyee_type,
                        'live_with' => $request->live_with,
                    ]
                );

                $response = __('auth.phone_update');
            }

            if ($request->verifytype == 'onlypass') {
                $user->password = Hash::make($request->new_password);
                $user->save();
                $response = __('auth.pass_update');
            }

            DB::commit();

            return ResponseBuilder::success(null, $response, $this->successStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }
        try {
            $user = Auth::user();
            if (!Hash::check($request->old_password, $user->password)) {
                return ResponseBuilder::error(__('auth.old_pass_incorrect'), $this->validationStatus);
            }
            $otpData = $this->generateUniqueCode();
            $otp = $otpData['code'];
            $otpGeneratedAt = $otpData['generated_at'];
            $otpVerification = $user->otpVerification()->firstOrNew([]);
            $otpVerification->otp = $otp;
            $otpVerification->otp_generated_at = $otpGeneratedAt;
            $otpVerification->save();
            Helper::sendOtp($user, $otp);
            $data = [
                'type' => 'verify',
                'verifytype' => 'onlypass',
                'email' => $user->email,
            ];
            return ResponseBuilder::success($data, __('auth.otp_send_successfully'));
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function set_message_schedule_time(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required',
            'end_time' => 'required',
            'schedule_type' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        try {
            $user = Auth::user();
            $user_id = $user->id;
            $startTimeUTC = $request->start_time;
            $endTimeUTC = $request->end_time;
            $subscription_id = $user?->active_subscription?->id;
            $data = [
                'start_time' => $startTimeUTC,
                'end_time' => $endTimeUTC,
                'schedule_type' => $request->schedule_type,
                'user_subscription_id' => $subscription_id
            ];

            UserScheduleTime::updateOrCreate([
                'user_id' => $user_id,
                'user_subscription_id' => $subscription_id
            ], $data);
            return ResponseBuilder::success('', __('message.schedule_time'));
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
    function getCurrentTimeInUTC($timezone = 'Africa/Johannesburg')
    {
        $dateTime = new DateTime('now', new DateTimeZone($timezone));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('H:i:s');
    }

    function convertToUTC($time, $timezone = null)
    {
        $auth = auth()->user();
        if (!$timezone) {
            if ($auth && $auth->timeZone) {
                $timezone = $auth->timeZone;
            } else {
                $timezone = 'Africa/Johannesburg';
            }
        }
        $today = new DateTime('now', new DateTimeZone($timezone));
        $dateString = $today->format('Y-m-d') . ' ' . $time;
        $dateTime = DateTime::createFromFormat('Y-m-d gA', $dateString, new DateTimeZone($timezone));
        $dateTime->setTimezone(new DateTimeZone('UTC'));
        return $dateTime->format('H:i:s');
    }

    public function message_alert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_alert' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        $user = Auth::user();
        try {
            $user_id = $user->id;
            $message_alert = $request->message_alert ? 1 : 0;
            User::where('id', $user_id)->update(['message_alert' => $message_alert]);

            return ResponseBuilder::success('', __('message.message_alert'));
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }

    public function transaction_history(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseBuilder::error('Unauthorized', 401);
        }
        $transaction_history = UserSubscription::with('plan', 'user_schedule_time')
            ->where('user_id', $user->id)
            ->where('pf_payment_id', '!=', '00001')
            ->get();
        $data = TranscationResource::collection($transaction_history);
        return ResponseBuilder::success($data, '');
    }

    public function update_employment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emplyee_type' => 'required|in:employed,contract,self_employed,student,retired,unemployed',
            'live_with' => 'required|numeric|digits_between:1,20',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return ResponseBuilder::error($errors, $this->validationStatus);
        }

        try {
            $user = Auth::user();
            $user_id = $user->id;
            UserEmployment::updateOrCreate(
                ['user_id' => $user_id],
                [
                    'emplyee_type' => $request->emplyee_type,
                    'live_with' => $request->live_with,
                ]
            );
            $data = new UserEmploymentResource($user->user_employment);
            return ResponseBuilder::success($data, __('message.details_update'));
        } catch (\Exception $e) {
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
}
