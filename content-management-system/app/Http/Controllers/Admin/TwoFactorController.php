<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class TwoFactorController extends Controller
{
    public function otp(Request $request)
    {
        $user = $request->user();
        $verification = $user->verifications()->orderBy('id', 'desc');
        if ($verification) {
            if ($verification->first()->status == 'used') {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('admin.otp_verity', compact('user'));
    }

    public function otp_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "otp" => "required|min:4",
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }


        $user = $request->user();

        $verification = $user->verifications()->where([
            "scope" => "verification",
            "verification_type" => "email",
            "status" => "pending",
        ])->where("valid_upto", ">", now())->where("verifying", $user->email)
            ->first();

        if (empty($verification)) {
            return back()->with(['error' => __('otp_expired')]);
        }

        if ($verification->otp != $request->otp) {
            return back()->with(['error' => __('auth.otp_invalid')]);
        }
        if ($user->verifyEmailOtp(otp: $request->otp)) {

            Session::put('pass_two_factor', $user->id);

            return redirect()->route('admin.dashboard')->with('');
        }
    }

    public function resendOtp(Request $request)
    {
        if ($request->ajax()) {
            $user = $request->user();
            $user->sendEmailVerificationNotification();
            return response()->json([
                'msg' => __('admin.otp_sent_successfully')
            ]);
        }
        // return redirect()->back()->with('success', __('admin.otp_sent_successfully'));
    }
}
