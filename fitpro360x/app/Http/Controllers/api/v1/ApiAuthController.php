<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Resources\UserResource;
use App\Mail\ForgotPasswordMail;
use App\Mail\SuccessfulSignupMail;

use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Verification;
use App\Traits\Common_trait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
//use subscription controller;
use App\Http\Controllers\api\v1\SubscriptionController;


class ApiAuthController extends BaseApiController
{

    use Common_trait;

    public function signup(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fullname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('ft_users', 'email')->whereNull('deleted_at'),
            ],
            'language' => 'required|in:1,2',
            'password' => 'required|min:6|confirmed',
            'device_id' => 'required|string',
            'device_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        try {
            $validated = $validator->validated();

            // Check if email exists
            if (!isset($validated['email'])) {
                return $this->sendError('Email is required', [], 422);
            }

            $email = strtolower($validated['email']);
            $deviceId = $validated['device_id'];

            // Check if user already exists
            $existingUser = User::where('email', $email)->whereNull('deleted_at')->first();
            if ($existingUser) {
                return $this->sendError('This email is already registered.', [], 422);
            }

            // Check verification status
            $isVerified = Verification::where('value', $email)
                ->where('device_id', $deviceId)
                ->where('type', 1)
                ->where('status', 1)
                ->exists();

            if (!$isVerified) {
                // Generate and send OTP if not verified
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

                $this->sendEmail($email, new OtpMail($otp));
                return $this->sendResponse(null, 'An OTP has been sent to your email address. Please verify it to complete the signup process.');
            }

            // Create user if verified
            $user = User::create([
                'fullname' => $validated['fullname'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'role' => 2,
                'language' => $validated['language'],
                'status' => 1,
                'device_id' => $deviceId
            ]);

            $token = $user->createToken(env('API_SECRET_KEY'))->plainTextToken;

            // Store last token ID
            $lastToken = $user->tokens()->latest()->first();
            if ($lastToken) {
                $user->last_token_id = $lastToken->id;
                $user->save();
            }
            Mail::to($user->email)->send(new SuccessfulSignupMail($user));
            // Clean up verification records
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

    public function sendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }
        if (!filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Invalid email address', [], 400);
        }

        $otp = rand(1000, 9999);
        $expiryTime = Carbon::now()->addMinutes(10);

        $verification = Verification::where('value', strtolower($request->value))
            ->where('type', 1)
            ->where('device_id', $request->device_id) // Ensure same device_id
            ->first();


        if ($verification && $verification->status == 1) {
            return $this->sendError('This Email is already verified', [], 400);
        }

        if ($verification) {
            if ($verification->device_id == $request->device_id) {
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            } else {
                $verification = new Verification();
                $verification->value = strtolower($request->value);
                $verification->type = 1; //1 = email verify
                $verification->device_type = $request->device_type;
                $verification->device_id = $request->device_id;
                $verification->otp = $otp;
                $verification->expires_at = $expiryTime;
                $verification->otp_type = 1;
                $verification->save();
            }
        } else {
            $verification = new Verification();
            $verification->value = strtolower($request->value);
            $verification->type = 1;
            $verification->device_type = $request->device_type;
            $verification->device_id = $request->device_id;
            $verification->otp = $otp;
            $verification->expires_at = $expiryTime;
            $verification->otp_type = 1;
            $verification->save();
        }

        if ($request->type == 'email') {
            $this->sendEmail($request->value, new OtpMail($otp));
            return $this->sendResponse($verification->value,  'OTP sent successfully via email');
        }
    }

    function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        if (!filter_var($request->value, FILTER_VALIDATE_EMAIL)) {
            return $this->sendError('Invalid email address', [], 400);
        }

        $otpRecord = Verification::where('value', strtolower($request->value))
            ->where('otp_type', 1) // OTP Type: 1 = Signup
            ->where('device_id', $request->device_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return $this->sendError('Account not found', [], 400);
        }

        if ($otpRecord->status == 1) {
            return $this->sendError('This account is already verified', [], 400);
        }

        if ($otpRecord->otp !== $request->otp) {
            return $this->sendError('Invalid OTP', [], 400);
        }

        if ($otpRecord->expires_at && Carbon::now()->gt($otpRecord->expires_at)) {
            return $this->sendError('OTP has expired', [], 400);
        }

        $otpRecord->status = 1;
        $otpRecord->save();

        return $this->sendResponse($otpRecord->value, message: 'OTP verified successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !ctype_digit($value)) {
                        $fail('The value must be a valid email or a numeric phone number.');
                    }
                },
            ],
            'password' => 'required|string|min:6', // Ensure password is provided and meets minimum length
            'device_type' => 'required|in:android,ios', // Ensure device type is either 'android' or 'ios'
            'device_id' => 'required', // Ensure device ID is provided for tracking the device
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        // Initialize the user variable to null.
        // We'll use withTrashed() to include soft-deleted users so that we can check if the user is soft-deleted.
        $user = null;

        // Check if the provided value is a valid email address or a numeric phone number.


        if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            //$user = User::withTrashed()->where('email', $request->email)->first();
            $user = User::where('email', $request->email)->whereNull('deleted_at')->first();
        }

        // If the user is not found, return an error response
        if (!$user) {
            return $this->sendError('User does not exist', [], 404);
        }

        // Check if the user is soft deleted
        if ($user->trashed()) {
            return $this->sendError('User does not exist', [], 404);
        }

        // Verify the password entered by the user
        if (!Hash::check($request->password, $user->password)) {
            // If the password is incorrect, return an error response
            return $this->sendError('Invalid credentials', [], 401);
        }
        // If user is already logged in from another device
        if ($user->device_id && $user->device_id !== $request->device_id) {
            // Revoke all previous tokens
            $user->tokens()->delete();
        }
        // Generate a new API token for the authenticated user
        $token = $user->createToken(env('API_SECRET_KEY'))->plainTextToken;

        $lastToken = $user->tokens()->latest()->first();

        if ($lastToken) {
            $user->last_token_id = $lastToken->id;
            $user->save();
        }

        // Update the user record with the device fields
        $user->update([
            'device_id'   => $request->device_id,
            'device_type' => $request->device_type,
        ]);

        DB::table('test')->insert([
            'data' => $user->id . '-' . json_encode($token),
        ]);


        // Return a successful response with the user data and the generated token
        return $this->sendResponse([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 'Login successfully');
    }


    public function logout(Request $request)
    {
        // Check if the user is authenticated (i.e., if a valid token exists)
        $user = $request->user();
        if (!$user) {
            return $this->sendError('Authentication token not provided or invalid. User does not exist.', [], 401);
        }

        // Revoke the current access token to "log out" the user
        $user->currentAccessToken()->delete();
        $user->tokens()->delete();

        // Optionally, update the user record to set device fields to null
        $user->update([
            'device_id'   => null,
            'device_type' => null,
        ]);

        // Return a success response
        return $this->sendResponse([
            //'user' => new UserResource($user)
        ], 'Logout successful');
    }

    public function checkEmail(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        $user = User::withTrashed()
            ->where('email', $request->input('email'))
            ->whereNull('deleted_at')
            //->where('status', '!=', 0)
            ->first();


        if (!$user) {
            return $this->sendResponse([
                'exists'  => false,
            ], 'Email does not exist.');
        }

        // Return a success response
        return $this->sendResponse([
            'exists'  => true
        ], 'Email already exists.');
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:6|max:15|confirmed|different:current_password',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, [], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('The current password is incorrect', [], 422);
        }

        $user->password = Hash::make($request->new_password);
        if ($user->save()) {
            return $this->sendResponse([], 'Password changed successfully');
        } else {
            return $this->sendError('Failed to change password. Please try again.', [], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $table = config('tables.users');

            $validator = Validator::make($request->all(), [
                'email' => "required|email|exists:$table,email",
            ]);

            if ($validator->fails()) {
                $firstError = collect($validator->errors()->all())->first();
                return $this->sendError($firstError, [], 422);
            }

            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendResponse([], 'Email Sent Successfully');
            }

            return $this->sendError(__($status));
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', $e->getMessage(), 500);
        }
    }


    public function showResetForm(Request $request, $token)
    {
        return view('reset_password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                // Check if new password is same as old one
                if (Hash::check($password, $user->password)) {
                    throw ValidationException::withMessages([
                        'password' => ['You cannot reuse your old password. Please choose a different one.'],
                    ]);
                }

                // Update to new password
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('thankyou')->with('message', 'Your password has been reset successfully. You can now log in to the app with your new password.');
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    public function destroy(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return $this->sendError('Unauthorized', [], 403);
        }
        if (auth()->user()->role === 1) {
            //$userId = request('user_id');
            return $this->sendError('User not Unauthorized', [], 404);
        }
        // Get the ID of the user to delete
        $userId = $user->id;

        try {
            $userToDelete = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('User does not exist', [], 404);
        }

        try {
            // $userToDelete->subscriptions()->delete();
            // $userToDelete->workoutPlans()->delete();
            // $userToDelete->questionAnswers()->delete();

            // $userToDelete->challengeProgress()->delete();
            // $userToDelete->exerciseProgress()->delete();
            // $userToDelete->userProgress()->delete();
            // $userToDelete->notifications()->delete();
            $userToDelete->delete();
            //call cancelAndroidSubscription api from subscvription controller 
            $subscriptionController = new SubscriptionController();
            $subscriptionController->cancelAndroidSubscription($request);
            // Optionally, you can also delete the user from the 'test' table if needed
            DB::table('test')->where('data', 'like', "%{$userId}%")->delete();
        } catch (\Exception $e) {
            return $this->sendError($e->GetMessage(), [], 500);
            return $this->sendError('Failed to delete related data', [], 500);
        }

        return $this->sendResponse([], 'User deleted successfully');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('User not Unauthorized', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return $this->sendError($firstError, null, 200);
        }

        if ($request->has('fullname')) {
            $user->fullname = $request->input('fullname');
        }

        if ($request->hasFile('profile_photo')) {
            $imagePath = $this->file_upload($request->file('profile_photo'), 'profile_photos');
            $user->profile_photo = $imagePath;
        }

        $user->save();

        $user->profile_photo = $user->profile_photo
            ? asset($user->profile_photo)
            : null;

        return $this->sendResponse(new UserResource($user),'Your profile has been updated successfully.');
    }

    public function sendSupportEmail(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return $this->sendError('User not Unauthorized', [], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $adminEmail = 'info@fitpro360x.com'; // Set your admin email here

        // Email content
        $mailData = [
            'name' => $user->fullname ?? $user->name,
            'email' => $user->email,
            'title' => $validated['title'],
            'description' => $validated['description'],
        ];

        Mail::send('emails.support', $mailData, function ($message) use ($adminEmail) {
            $message->to($adminEmail)->subject('New Support Request Submitted - FitPro360X');
        });

        return $this->sendResponse([], 'Support request sent successfully');
    }
}
