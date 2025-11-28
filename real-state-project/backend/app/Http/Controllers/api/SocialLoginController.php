<?php

namespace App\Http\Controllers\api;

use App\Helpers\ResponseBuilder;
use App\Http\Controllers\Controller;
use App\Models\Plans;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;


class SocialLoginController extends Controller
{

    public function social_login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'social_type' => 'required|in:google,microsoft',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->first();
                return ResponseBuilder::error($errors, $this->validationStatus);
            }
            $token = $request->token;
            $social_type = $request->social_type;
            if ($social_type == 'google') {
                $social = 'google';
            } else {
                $social = 'microsoft';
            }
            // Retrieve the Google user information
            $socialUser = Socialite::driver($social)->userFromToken($token);
            // Extract Google user ID and email
            $social_id = $socialUser->getId();
            $social_email = $socialUser->getEmail();
            // $google_image = $socialUser->getAvatar();

            // Check if the user already exists in the database
            $existingUser = User::Where('email', $social_email)->first();

            $subscription_id = Plans::where('plan_name', 'Professional')->first();
            $started_at = Carbon::now();
            $expired_at = $started_at->copy()->addMonth();

            if ($existingUser) {
                // User exists, log them in and generate an access token
                if (is_null($existingUser->social_id)) {
                    $existingUser->social_id = $social_id;
                    $existingUser->name = $socialUser->getName();
                    $existingUser->social_type = $social;
                    $existingUser->save();
                }
                Auth::login($existingUser);
                if ($existingUser->otpVerification && $existingUser->otpVerification->otp_verified_at) {
                    $phone = $existingUser->phone;
                    $country_code = $existingUser->country_code;
                } else {
                    $phone = null;
                    $country_code = null;
                }
                /* start static subscription remove this after client feedback */
                // $subdata = [
                //     'user_id' => $existingUser->id,
                //     'subscription_id' => $subscription_id->id,
                //     'amount' => '0.00',
                //     'status' => 'ongoing',
                //     'started_at' => $started_at,
                //     'expired_at' => $expired_at
                // ];
                // UserSubscription::create($subdata);
                // $existingUser->update(['subscription' => 1]);
                /* end  */
                $data = [
                    'token' => $existingUser->createToken('pocketproperty')->accessToken,
                    'user' => [
                        'name' => $existingUser->name,
                        'email' => $existingUser->email,
                        'subscription' => $existingUser->subscription,
                        'type' => 'user',
                        'login_type' => $existingUser->social_type,
                        'phone' => $phone, // Default to null, can be updated later
                        'country_code' => $country_code, // Default to null, can be updated later
                    ],
                ];
                return ResponseBuilder::success($data, __('auth.login'), $this->successStatus);
            } else {
                // User does not exist, create a new user record
                $newUser = User::create([
                    'name' => $socialUser->name,
                    'email' => $social_email,
                    'type' => 'user',
                    'social_type' => $social,
                    // 'image' => $image,
                    'phone' => null, // Default to null, can be updated later
                    'country_code' => null, // Default to null, can be updated later
                    'social_id' => $social_id,
                    'password' => Hash::make(Str::random(32)),
                ]);
                /* start static subscription remove this after client feedback */
                $subdata = [
                    'user_id' => $newUser->id,
                    'subscription_id' => $subscription_id->id,
                    'amount' => '0.00',
                    'status' => 'ongoing',
                    'started_at' => $started_at,
                    'expired_at' => $expired_at
                ];
                UserSubscription::create($subdata);
                $newUser->update(['subscription' => 1]);
                /* end */
                // Log the new user in and generate an access token
                Auth::login($newUser);
                $data = [
                    'token' => $newUser->createToken('pocketproperty')->accessToken,
                    'user' => [
                        'name' => $newUser->name,
                        'email' => $newUser->email,
                        'subscription' => $newUser->subscription,
                        'type' => 'user',
                        'login_type' => $newUser->social_type,
                        'phone' => null, // Default to null, can be updated later
                        'country_code' => null, // Default to null, can be updated later
                    ],
                ];
                return ResponseBuilder::success($data, __('auth.login'), $this->successStatus);
            }
        } catch (\Exception $e) {
            // Return an error response
            return ResponseBuilder::error($e->getMessage(), $this->errorStatus);
        }
    }
}
