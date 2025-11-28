<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Resources\SubscriptionPlanResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WorkoutFrequencyResource;
use App\Mail\SuccessfulSignupMail;
use App\Mail\OtpMail;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Verification;
use App\Traits\Common_trait;
use Carbon\Carbon;
use App\Models\WorkoutFrequency;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Traits\UserWorkoutCloner;
use Illuminate\Validation\Rules\Password as RulesPassword;

class ApiAuthController extends BaseApiController
{
    use Common_trait, UserWorkoutCloner;

    public function signup(Request $req)
    {
        $v = Validator::make($req->all(), [
            'fullname' => 'required|string|max:100|min:2',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email')->whereNull('deleted_at'),
            ],
            'language' => 'required|in:1,2',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed',
                RulesPassword::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
            'device_id' => 'required|string',
            'device_type' => 'required|string',
        ], [
            'password.required' => 'A password is required.',
            'password.min' => 'The password must be at least 6 characters long.',
            'password.max' => 'The password may not be greater than 15 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.mixedCase' => 'The password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one special character.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        try {
            $validated = $v->validated();
            if (!isset($validated['email'])) {
                return $this->sendError('Email is required', [], 422);
            }
            $email = strtolower($validated['email']);
            $deviceId = $validated['device_id'];
            $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();
            if ($existingUser) {
                return $this->sendError('This email is already registered.', [], 422);
            }
            $isVerified = Verification::where('value', $email)
                ->where('device_id', $deviceId)
                ->where('type', 1)
                ->where('status', 1)
                ->exists();
            if (!$isVerified) {
                $otp = rand(1000, 9999);
                $expiryTime = Carbon::now()->addMinutes(10);
                $verification = Verification::updateOrCreate(
                    [
                        'value' => $email,
                        'device_id' => $deviceId,
                        'type' => 1
                    ],
                    [
                        'device_type' => $validated['device_type'],
                        'otp' => $otp,
                        'expires_at' => $expiryTime,
                        'otp_type' => 1,
                        'status' => 0
                    ]
                );
                if ($verification) {
                    $this->sendEmail($email, new OtpMail($otp));
                    return $this->sendResponse(null, 'An OTP has been sent to your email address. Please verify it to complete the signup process.');
                }
            }
            $user = User::create([
                'fullname' => $validated['fullname'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 2,
                'language' => $validated['language'],
                'status' => 1,
                'device_id' => $deviceId,
                'login_type' => 1,
            ]);
            $token = $user->createToken(config('app.api.secret_key'))->plainTextToken;
            $lastToken = $user->tokens()->latest()->first();
            if ($lastToken) {
                $user->last_token_id = $lastToken->id;
                $user->save();
            }
            Mail::to($user->email)->send(new SuccessfulSignupMail($user));
            Verification::where('value', $email)
                ->where('device_id', $deviceId)
                ->where('type', 1)
                ->delete();
            return $this->sendResponse([
                'user' => new UserResource($user),
                'token' => $token,
            ], 'User registered successfully');
        } catch (\Exception $e) {
            return $this->sendError('Some error occurred', [$e->getMessage()], 500);
        }
    }

    public function sendOtpVerification(Request $req)
    {
        $v = Validator::make($req->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (!ctype_digit($value)) {
                        $fail('The value must be a valid email .');
                    }
                },
            ],
            'type' => 'required|in:email',
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        if (!filter_var($req->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Invalid email address', [], 400);
        }
        $otp = rand(1000, 9999);
        $expiryTime = Carbon::now()->addMinutes(10);
        $verification = Verification::where('value', strtolower($req->value))
            ->where('type', 1)
            ->where('device_id', $req->device_id)
            ->first();
        if ($verification && $verification->status == 1) {
            return $this->sendError('This Email is already verified', [], 400);
        }
        if ($verification) {
            if ($verification->device_id == $req->device_id) {
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            } else {
                $verification = new Verification();
                $verification->value = strtolower($req->value);
                $verification->type = 1; //1 = email verify
                $verification->device_type = $req->device_type;
                $verification->device_id = $req->device_id;
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            }
        } else {
            $verification = new Verification();
            $verification->value = strtolower($req->value);
            $verification->type = 1;
            $verification->device_type = $req->device_type;
            $verification->device_id = $req->device_id;
            $verification->otp = $otp;
            $verification->expires_at = $expiryTime;
            $verification->otp_type = 1;
            $verification->save();
        }
        if ($req->type == 'email') {
            $this->sendEmail($req->value, new OtpMail($otp));
            return $this->sendResponse($verification->value, 'OTP sent successfully via email');
        }
    }
    public function verifyOtp(Request $req)
    {
        $v = Validator::make($req->all(), [
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return;
                    }
                    if (!ctype_digit($value)) {
                        $fail('The value must be a valid email .');
                    }
                },
            ],
            'type' => 'required|in:email',
            'otp' => 'required',
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        try {
            if (!filter_var($req->value, FILTER_VALIDATE_EMAIL)) {
                return $this->sendError('Invalid email address', [], 400);
            }
            $otpRecord = Verification::where('value', strtolower($req->value))
                ->where('otp_type', 1)
                ->where('device_id', $req->device_id)
                ->orderBy('created_at', 'desc')
                ->first();
            if (!$otpRecord) {
                return $this->sendError('Account not found', [], 400);
            }
            if ($otpRecord->status == 1) {
                return $this->sendError('This account is already verified', [], 400);
            }
            if ($otpRecord->otp !== $req->otp) {
                return $this->sendError('Invalid OTP', [], 400);
            }
            if ($otpRecord->expires_at && Carbon::now()->gt($otpRecord->expires_at)) {
                return $this->sendError('OTP has expired', [], 400);
            }
            $otpRecord->status = 1;
            $otpRecord->save();
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', [$e->getMessage()], 500);
        }
        return $this->sendResponse($otpRecord->value, message: 'OTP verified successfully');
    }

    public function login(Request $req)
    {
        $v = Validator::make($req->all(), [
            'email' => 'nullable|email',
            'phone' => 'nullable|digits_between:8,15',
            'password' => 'required|string|min:6',
            'device_type' => 'required|in:android,ios',
            'device_id' => 'required',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        $user = null;
        if (filter_var($req->email, FILTER_VALIDATE_EMAIL)) {
            $user = User::withTrashed()
                ->where('email', $req->email)
                ->first();
        }
        if (!$user) {
            return $this->sendError(__('messages.not_exist', ['item' => 'User']), [], 404);
        }
        if ($user->trashed()) {
            return $this->sendError(__('messages.deleted_account'), [], 403);
        }
        if (!Hash::check($req->password, $user->password)) {
            return $this->sendError('Invalid credentials', [], 401);
        }
        if ($user->status == 0) {
            return $this->sendError(__('messages.deactivate_account'), [], 403);
        }
        if ($user->device_id && $user->device_id !== $req->device_id) {
            // $user?->tokens()->delete(); // delete all old tokens
        }
        $token = $user->createToken(config('app.api.secret_key'))->plainTextToken;
        $lastToken = $user->tokens()->latest()->first();
        if ($lastToken) {
            $user->last_token_id = $lastToken->id;
        }
        $user->device_id = $req->device_id;
        $user->device_type = $req->device_type;
        $user->save();
        return $this->sendResponse([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successfully');
    }

    public function logout(Request $req)
    {
        $user = $req->user();
        if (!$user) {
            return $this->sendError('Authentication token not provided or invalid. User does not exist.', [], 401);
        }
        $user->currentAccessToken()->delete();
        $user->tokens()->delete();
        $user->update([
            'device_id' => null,
            'device_type' => null,
        ]);
        return $this->sendResponse([], 'Logout successful');
    }

    public function checkEmail(Request $req)
    {
        $v = Validator::make($req->all(), [
            'email' => 'required|email',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        $user = User::withTrashed()
            ->where('email', $req->input('email'))
            ->whereNull('deleted_at')
            ->first();
        if (!$user) {
            return $this->sendResponse([
                'exists' => false,
            ], __('messages.not_exist', ['item' => 'Email']));
        }
        return $this->sendResponse([
            'exists' => true
        ], 'Email already exists.');
    }

    public function changePassword(Request $req)
    {
        $user = $req->user();
        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }
        $v = Validator::make($req->all(), [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed',
                'different:current_password',
                RulesPassword::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'current_password.required' => 'Your current password is required.',
            'new_password.required' => 'A new password is required.',
            'new_password.min' => 'The new password must be at least 6 characters long.',
            'new_password.max' => 'The new password may not be greater than 15 characters.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'new_password.different' => 'The new password must be different from your current password.',
            'new_password.mixedCase' => 'The new password must contain both uppercase and lowercase letters.',
            'new_password.numbers' => 'The new password must contain at least one number.',
            'new_password.symbols' => 'The new password must contain at least one special character.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        if (!Hash::check($req->current_password, $user->password)) {
            return $this->sendError('The current password is incorrect', [], 422);
        }
        $user->password = Hash::make($req->new_password);
        if ($user->save()) {
            return $this->sendResponse([], "For security reasons, you’ll need to log in again using your new password");
        } else {
            return $this->sendError('Failed to change password. Please try again.', [], 500);
        }
    }

    public function forgotPassword(Request $req)
    {
        try {
            $v = Validator::make($req->all(), [
                'email' => "required|email|exists:" . User::class . ",email",
            ], [
                'email.exists' => 'No account found with this email address'
            ]);
            if ($v->fails()) {
                $firstError = collect($v->errors()->all())->first();
                return $this->sendError($firstError, [], 422);
            }
            $user = User::whereEmail($req->email)->first();
            if (!$user) {
                return $this->sendError(__('messages.not_exist', ['item' => 'User']), [], 404);
            }
            if ($user->trashed()) {
                return $this->sendError(__('messages.deleted_account'), [], 403);
            }
            if ($user->status == 0) {
                return $this->sendError(__('messages.deactivate_account'), null, 403);
            }
            DB::table('password_reset_tokens')->where('email', $req->email)->delete();
            $status = Password::sendResetLink($req->only('email'));
            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendResponse([], "A password reset link has been sent to your email");
            }
            return $this->sendError(__($status));
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), 500);
        }
    }

    public function showResetForm(Request $req)
    {
        $token = $req->query('token');
        $email = $req->query('email');
        $errors = [];
        $user = User::whereEmail($email)->first();
        $reset = DB::table('password_reset_tokens')->whereEmail($email)->first();
        if (!$user) {
            $errors['email'] = 'This email is not registered';
        } elseif (!$reset) {
            $errors['email'] = 'The reset link is expired';
        } elseif (Carbon::parse($reset->created_at)->lt(now()->subMinutes(60))) {
            $errors['email'] = 'The reset Token is expired';
        } else {
            $errors['email'] = null;
        }
        if (!empty($errors)) {
            return view('reset_password', compact('token', 'email'))->withErrors($errors);
        }
        return view('reset_password', compact('token', 'email'));
    }

    public function reset(Request $req)
    {
        $v = Validator::make($req->all(), [
            'token' => 'required',
            'email' => 'required|email|exists:password_reset_tokens,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'confirmed',
                RulesPassword::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'email.exists' => 'This email is not registered or link is expired',
            'password.required' => 'A password is required',
            'password.min' => 'The password must be at least 6 characters long.',
            'password.max' => 'The password may not be greater than 15 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.mixedCase' => 'The password must contain both uppercase and lowercase letters.',
            'password.numbers' => 'The password must contain at least one number.',
            'password.symbols' => 'The password must contain at least one special character.',
        ]);
        $email = $req->email;
        $token = $req->token;
        if ($v->fails()) {
            return view('reset_password', compact('token', 'email'))->withErrors($v);
        }
        try {
            $status = Password::reset(
                $req->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) use ($req) {
                    if (Hash::check($password, $user->password)) {
                        throw ValidationException::withMessages([
                            'password' => 'You cannot reuse your old password. Please choose a different one.',
                        ]);
                    } else {
                        $user->forceFill([
                            'password' => Hash::make($password),
                        ])->save();
                    }
                }
            );
        } catch (ValidationException $e) {
            return view('reset_password', compact('token', 'email'))->withErrors($e->errors());
        }
        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('thankyou')->with('message', 'Your password has been reset successfully. You can now log in to the app with your new password.');
        }
        return view('reset_password', compact('token', 'email'))->withErrors(['email' => [__($status)]]);
    }

    public function destroy(Request $req)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return $this->sendError('Unauthorized', [], 403);
        }
        if ($req->user()->role === 1) {
            return $this->sendError('User not Unauthorized', [], 404);
        }
        $userId = $currentUser->id;
        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('User does not exist', [], 404);
        }
        try {
            $user->resetData(true);
            $user?->currentAccessToken()?->delete();
            $user?->tokens()?->delete();
            $user->delete();
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete related data', [], 500);
        }
        return $this->sendResponse([], 'User deleted successfully');
    }

    public function updateProfile(Request $req)
    {
        $user = $req->user();
        if (!$user) {
            return $this->sendError('User not Unauthorized', [], 404);
        }
        $msg = 'Your profile has been updated successfully.';
        $v = Validator::make($req->all(), [
            'fullname' => 'required|string|max:255',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'workout_frequency' => 'required|exists:' . WorkoutFrequency::class . ',id'
        ], [
            'workout_frequency.exists' => 'The selected workout frequency is invalid.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, null, 200);
        }
        if ($req->has('fullname')) {
            $user->fullname = ucfirst($req->input('fullname'));
        }
        if ($req->workout_frequency) {
            $frequency = WorkoutFrequency::find($req->workout_frequency);
            if (!$user->workout_frequency) {
                $this->cloneWorkoutsForUser($user->id, 1, 1, $frequency->id);
            } else if ($req->workout_frequency != $user->workout_frequency) {
                $msg = 'Your updated day frequency will be effective from next week plan.';
            }
            $user->workout_frequency = $frequency->id;
        }
        if ($req->hasFile('profile_photo')) {
            $imagePath = $this->file_upload($req->file('profile_photo'), 'profile_photos');
            $user->profile_photo = $imagePath;
        }
        $user->save();
        $user->profile_photo = $user->profile_photo
            ? asset($user->profile_photo)
            : null;
        $user->is_subscribe = (bool) ($user->is_subscribe == 1 ? true : false);
        return $this->sendResponse($user, $msg);
    }

    public function sendSupportEmail(Request $req)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->sendError('User not Unauthorized', [], 404);
        }
        $validated = $req->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        $adminEmail =  config('mail.support.email');
        $mailData = [
            'name' => $user->fullname ?? $user->name,
            'email' => $user->email,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ];
        Mail::send('emails.support', $mailData, function ($message) use ($adminEmail) {
            $message->to($adminEmail)->subject('New Support Request Submitted - ' . config('app.name'));
        });
        return $this->sendResponse([], 'Support request sent successfully');
    }

    public function resetPassword(Request $req)
    {
        $v = Validator::make($req->all(), [
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'max:15',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'device_type' => 'required|in:1,2',
            'device_id' => 'required',
        ], [
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password must not exceed 15 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must include uppercase, lowercase, number, and special character.',
        ]);
        if ($v->fails()) {
            $firstError = collect($v->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        $user = User::where('email', strtolower($req->email))->first();
        if (!$user) {
            return $this->sendError(__('messages.not_found', ['item' => 'User']), [], 404);
        }
        if ($user->status == 0) {
            return $this->sendError(__('messages.account_is_deactivate'), null, 403);
        }
        if (Hash::check($req->password, $user->password)) {
            return $this->sendError(__('messages.you_cannot_reuse_your_old_password'), [], 422);
        }
        $user->password = Hash::make($req->password);
        $user->save();
        return $this->sendResponse([], __('messages.password_reset_successful'));
    }

    public function resetProfile(Request $req)
    {
        $currentUser = $req->user();
        try {
            $user = User::findorfail($currentUser->id);
            if (!$this->is_eligible_for_reset($user)) {
                return ApiResponse::error(__('messages.restart_meso'), 401);
            }
            $user->resetData(false);
            $this->cloneWorkoutsForUser($user->id, 1, 1, $user->workout_frequency);
            return ApiResponse::success([], 'Profile reset sucessfully');
        } catch (\Exception $e) {
            return $this->sendError('Some error occurred', [$e->getMessage()], 500);
        }
    }

    public function userProfileDetail(Request $req)
    {
        $currentUser = $req->user();
        $user = User::findorfail($currentUser->id);
        $subscription = SubscriptionPlan::whereStatus('1')->first();
        $reset_status = $this->is_eligible_for_reset($user);
        return ApiResponse::success([
            'notifications_status' => $user->notifications_enabled == 1 ? true : false,
            'subscription_details' => $user->is_subscribe ? new SubscriptionPlanResource($subscription) : null,
            'reset_profile_status' => $reset_status,
            'purchase_again' => !$reset_status,
        ]);
    }

    public function profile(Request $req)
    {
        $currentUser = $req->user();
        if (!$currentUser) {
            return $this->sendError('User not Unauthorized', [], 404);
        }
        $user = User::with(['work_out_frequency'])->findorfail($currentUser->id);
        $frequency = WorkoutFrequency::select('id', 'name')->get();
        return ApiResponse::success([
            'data' => new UserResource($user),
            'frequency' => WorkoutFrequencyResource::collection($frequency),
        ]);
    }

    public function update_notifications_status(Request $req)
    {
        $currenctUser = $req->user();
        $user = User::find($currenctUser->id);
        $status = [
            1 => 'active',
            0 => 'inactive'
        ];
        $user->notifications_enabled = !$user->notifications_enabled;
        $user->save();
        return ApiResponse::success([], 'Notification status ' . $status[$user->notifications_enabled] . ' successfully.');
    }
}
