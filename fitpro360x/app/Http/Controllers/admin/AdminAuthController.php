<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AdminAuthController extends Controller
{


    public function loginAuth(Request $req)
    {
        $req->validate(
            [
                'email' => 'required|email',
                'password' => 'required',
            ]
        );
        $credentials = ['email' => $req->input('email'), 'password' => $req->input('password'), 'role' => 1];

        if (Auth::guard('admin')->attempt($credentials)) {

            return redirect()->route('admin.dashboard');
        } else {

            // return back()->with('flash-error', 'Invalid credentials')->withInput();
            return redirect()->back()->withErrors(['email' => 'Invalid credentials. Please try again.']);
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        session()->flush();
        return redirect()->route('admin.login');
    }

    public function showLinkRequestForm()
    {
        return view('admin.auth.forgot');
    }

    public function updatePassword(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|confirmed|different:current_password',
            'new_password_confirmation' => 'required|string|min:3',
        ]);

        $admin = Auth::guard('admin')->user();

        // Check if the current password matches the authenticated user's password
        if (!\Hash::check($request->current_password, $admin->password)) {
            // If it doesn't, return an error message
            return back()->withErrors(['current_password' => 'The provided current password is incorrect.']);
        }

        // If the validation passes, proceed to update the password
        try {
            $admin->password = \Hash::make($request->new_password);
            $admin->save();  // Save the updated password in the database

            // Return a success response
            return back()->with('success', 'Password updated successfully!');
        } catch (\Exception $e) {
            // In case of an error, return an error response
            return back()->withErrors(['error' => 'Failed to update the password. Please try again.']);
        }
    }

    public function changePassowrd()
    {
        return view('admin.auth.change_pass');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = User::where('email', $request->email)->where('role', 1)->first();

        // If it's not an admin, return an error
        if (!$admin) {
            return back()->withErrors(['email' => 'We couldn’t find an account with that email address.']);
        }


        // Use the default password broker to send the reset link to the email
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    // public function showResetForm(Request $request, $token)
    // {
    //     return view('admin.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    // }
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        // Check if the token is valid
        $tokenExists = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$tokenExists) {
            return view('admin.auth.reset-password', [
                'isExpired' => true,
                'email' => $email
            ]);
        }

        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'isExpired' => false
        ]);
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userInfo = User::where('email', $request->email)->first();

        // $isAdmin = 0;
        // if($userInfo->role == 1){
        //     $isAdmin = 1;
        // }

        $isAdmin = ($userInfo->role == 1);



        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),


            function ($user, $password) {

                if (Hash::check($password, $user->password)) {
                    throw ValidationException::withMessages([
                        'password' => ['You cannot reuse your old password. Please choose a different one.'],
                    ]);
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    // 'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($isAdmin) {
                return redirect()->route('login')->with('status', __($status));
            } else {
                return redirect()->route('thankyou')->with('message', 'Your password has been reset successfully. You can now log in to the app with your new password.');
            }
        }

        // Show specific error messages for various statuses
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => trans($status)]);
    }
}
