<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::check()){
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }
    public function login(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }
        $user = User::role(['admin', 'subadmin'])->where('email', $request->email)->where('is_active', 1)->first();


        if (!$user) {
            return back()->with(['error' => __('admin.email_not_registered')])->withInput($request->all());
        }


        if (!$user->hasRole(['admin', 'subadmin'])) {
            return back()->with(['error' => __('admin.email_not_registered')])->withInput($request->all());
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember_me ?? false)) {
            return back()->with(['error' => __('admin.invalid_credentials')])->withInput($request->all());
        }

        $user->sendEmailVerificationNotification();

        return to_route('admin.login.otp');
    }

    public function logout()
    {
        Auth::logout();
        Session::forget('pass_two_factor');
        return redirect()->route('login');
    }
}
