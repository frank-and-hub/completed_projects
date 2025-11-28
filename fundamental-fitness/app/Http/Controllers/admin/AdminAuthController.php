<?php

namespace App\Http\Controllers\admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, Mail};
use App\Mail\{AdminTwoFactorCodeMail, ForgotPasswordMail};
use App\Models\{User, Verification, PasswordResetToken};
use Carbon\Carbon;
use App\Traits\Common_trait;

class AdminAuthController extends Controller
{
    use Common_trait;
    public function loginAuth(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $email = $req->input('email');
        $password = $req->input('password');
        // Lookup admin user
        $user = User::where('email', $email)->where('role', 1)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->with('flash-error', 'Invalid credentials')->withInput();
        }
        $otp = rand(100000, 999999);
        Verification::where('value', $email)->delete(); // clear old otp
        $verification = new Verification();
        $verification->otp = $otp;
        $verification->type = 1; // login
        $verification->otp_type = 3; // email
        $verification->value = $email;
        $verification->expires_at = now()->addMinutes(10);
        $verification->save();
        // Send OTP mail
        Mail::to($email)->send(new AdminTwoFactorCodeMail($otp, $user->fullname));
        // Save session so we know who is verifying
        session([
            'admin_2fa_user_id' => $user->id,
            'admin_2fa_email'   => $email,
        ]);
        if ($req->ajax()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('admin.otpForm')
            ]);
        }
        return redirect()->route('admin.otpForm')->with('flash-success', 'OTP sent to your email.');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        session()->flush();
        return redirect()->route('admin.login');
    }

    public function otpForm()
    {
        if (!session('admin_2fa_email')) {
            return redirect()->route('admin.loginAuth');
        }
        return view('admin.auth.otp_verify');
    }

    public function verifyOtp(Request $req)
    {
        // Combine the OTP array into a single string
        $otp = is_array($req->otp) ? implode('', $req->otp) : $req->otp;
        // Validate OTP
        $req->merge(['otp_combined' => $otp]); // merge into request for validation
        $req->validate([
            'otp_combined' => 'required|digits:6',
        ], [
            'otp_combined.required' => 'Verification code is required.',
            'otp_combined.digits' => 'Verification code must be 6 digits.',
        ]);
        // Retrieve user based on session email
        $verification = Verification::where('value', session('admin_2fa_email'))->first();
        if (!$verification) {
            if ($req->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
                ], 400);
            }
            return redirect()->route('admin.login')->with('flash-error', 'Session expired. Please login again.');
        }
        if ($verification->otp !== $otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP entered.'
            ], 400);
        }

        if (now()->greaterThan($verification->expires_at)) {
            if ($req->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your OTP has expired. Please request a new one.'
                ], 400);
            }
            return back()->with('flash-error', 'Your OTP has expired. Please request a new one.')->withInput();
        }
        // OTP verified successfully
        $verification->delete();
        $user = User::where('email', session('admin_2fa_email'))->first();
        session()->forget('admin_2fa_email');
        if ($user) {
            Auth::guard('admin')->loginUsingId($user->id);
        }
        if ($req->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect_url' => route('admin.dashboard')
            ]);
        }
        return redirect()->route('admin.dashboard')->with('flash-success', 'Login successful.');
    }

    public function resendOtp(Request $request)
    {
        $email = session('admin_2fa_email');
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ], 400);
        }
        $user = User::where('email', $email)->where('role', 1)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 400);
        }
        // Delete any old OTPs for this email
        Verification::where('value', $email)->delete();
        // Generate new OTP
        $otp = rand(100000, 999999);
        // Insert new OTP record
        $verification = new Verification();
        $verification->otp = $otp;
        $verification->type = 1; // login
        $verification->otp_type = 3; // email
        $verification->value = $email;
        $verification->expires_at = now()->addMinutes(10);
        $verification->save();
        // Send OTP via same mail template
        try {
            // Mail::to($email)->send(new AdminTwoFactorCodeMail($otp));
            Mail::to($email)->send(new AdminTwoFactorCodeMail($otp, $user->first_name));

            return response()->json([
                'success' => true,
                'message' => 'New OTP sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);
        $admin = Auth::guard('admin')->user();
        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'The provided current password is incorrect.']);
        }
        $request->validate([
            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:15',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
                'different:current_password',
            ],
            'new_password_confirmation' => 'required|string|same:new_password',
        ], [
            'new_password.regex' => 'Password must have at least one uppercase letter (A-Z), one special character, and one digit.',
            'new_password_confirmation.same' => 'Confirm password must match the new password.',
        ]);
        try {
            $adminUser = User::find($admin->id);
            $adminUser->password = Hash::make($request->new_password);
            $adminUser->save();
            return back()->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update the password. Please try again.']);
        }
    }

    public function changePassowrd()
    {
        return view('admin.auth.change_pass');
    }

    public function sendResetToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        // Check if user exists and is role 1 (admin)
        if (!$user || $user->role != 1) {
            return back()->withErrors(['email' => 'Email not found']);
        }
        $token = bin2hex(random_bytes(16));
        $createdAt = Carbon::now();
        // Save token
        PasswordResetToken::updateOrCreate(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => $createdAt]
        );
        // Reset link
        $resetLink = url('/admin/password/reset/' . $token);
        // Send email
        $this->sendEmail(
            $request->email,
            new ForgotPasswordMail($resetLink, $user->fullname)
        );
        return back()->with('flash-success', 'Password reset link sent to your email.');
    }

    public function showResetForm($token)
    {
        $tokenData = PasswordResetToken::where('token', $token)->first();
        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', 'Invalid or expired token');
        }
        return view('admin.auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'new_password_confirmation' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:15',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/',
            ],
        ], [
            'new_password.regex' => 'Password must have at least one uppercase letter (A-Z), one special character, and one digit.',
        ]);
        $tokenData = PasswordResetToken::where('token', $request->token)->first();
        if (!$tokenData || Carbon::parse($tokenData->created_at)->addMinutes(15)->isPast()) {
            return redirect()->route('admin.forgotPassword')->with('flash-error', 'Invalid or expired token');
        }
        $user = User::where('email', $tokenData->email)->first();
        if (!$user) {
            return redirect('/password/request')->withErrors(['email' => 'User not found']);
        }
        $user->password = Hash::make($request->new_password);
        if ($user->save()) {
            $tokenData->delete();
            return redirect()->route('admin.login')->with('flash-success', 'Password reset successfully');
        }
        return redirect()->route('admin.forgotPassword')->with('flash-error', 'Something error occurred');
    }

    public function testUser($userId)
    {
        $user = User::find($userId);
        return ApiResponse::success(new UserResource($user));
    }
}
