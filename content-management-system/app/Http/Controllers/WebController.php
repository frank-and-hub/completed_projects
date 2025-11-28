<?php

namespace App\Http\Controllers;

use App\Models\CustomPage;
use App\Models\DeleteAccountRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WebController extends Controller
{
    public function terms_and_conditions()
    {
        $custom_page = CustomPage::where('slug', 'terms-conditions')->first();
        return view('terms_and_conditions', compact('custom_page'));
    }

    public function privacy_policy()
    {
        $custom_page = CustomPage::where('slug', 'privacy-policy')->first();
        return view('privacy_policy', compact('custom_page'));
    }

    public function delete_account()
    {
        return view('delete_account');
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
            'name' => 'required|string|max:255',
            'reason' => 'required|max:255|string'
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first())->withInput($request->all())->withErrors($validator->errors()->first());
        }

        $user = User::role(['user'])->where('email', $request->email)->where('is_active', 1)->first();


        if (!$user) {
            return back()->with(['error' => __('admin.email_not_registered')])->withInput($request->all());
        }


        if (!$user->hasRole(['user'])) {
            return back()->with(['error' => __('admin.email_not_registered')])->withInput($request->all());
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->with(['error' => __('admin.invalid_credentials')])->withInput($request->all());
        }

        $delete_account = DeleteAccountRequest::where('user_id', $user->id)->first();

        if ($delete_account) {
            return back()->with(['error' => __('Delete request already sended')])->withInput($request->all());
        }

        DeleteAccountRequest::create([
            'user_id' => $user->id,
            'reason' => $request->reason
        ]);


        return redirect()->route('thank-you');
    }

    public function thank_you()
    {
        return view('thank_you');
    }
}
