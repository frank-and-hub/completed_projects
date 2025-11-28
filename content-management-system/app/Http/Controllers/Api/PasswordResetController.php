<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Send request to Reset the password
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.

        $user = User::where(['email' => $request->email])->first();

        if (!$user) {
            return YResponse::json(message: __("passwords.user_email"), status: 400);
        }

        if ($user->is_active == false) {
            return YResponse::json(__('api_message.account_deactivated'), status: 400);
        }

        $status = Password::sendResetLink(
            $request->only('email'),
        );

        if ($status == Password::RESET_LINK_SENT) {

            $token = $user->createToken(abilities: ['reset-password']);

            $data = [
                "token" => $token->plainTextToken,
            ];

            return YResponse::json(
                message: __("passwords.reset_otp_sent"),
                data: $data
            );
        } else {
            //  Log::debug(["PASSWORD RESET API: ", $status]);

            return YResponse::json(message: __($status ? $status : 'errors.some_problem'), status: 400);
        }

        return YResponse::json(message: __("passwords.user_email"), status: 400);
    }

    /**
     * Here user will verify his identity so we can reset the password
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function check_otp(Request $request): JsonResponse
    {
        $request->validate([
            "otp" => "required|min:4",
        ]);

        $user = $request->user();

        $verification = $user->verifications()->where([
            "scope" => "reset_password",
            "status" => "pending",
            "verification_type" => "email",
            "verifying" => $user->email,
        ]);

        $verification = $verification->where("valid_upto", ">", now())
            ->first();

        if (empty($verification)) {
            return YResponse::json(__('auth.otp_expired'), status: 400);
        }

        if ($verification->otp != $request->otp) {
            return YResponse::json(__('auth.otp_invalid'), status: 400);
        }

        // VERIFY THE OTP

        $verification = $user->verifyResetPasswordOtp(otp: $request->otp);


        if ($verification) {
            $user->save();
            $data = [
                "fingerprint" => $verification->link,
            ];
            return YResponse::json(__('auth.reset_password_otp_verified_success'), data: $data);
        }

        return YResponse::json(__('auth.otp_not_matched'), status: 400);
    }

    public function change_password(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->numbers()->mixedCase()],
            'fingerprint' => ['required', 'string'],
        ]);

        $user = $request->user();
        $status = null;

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.

        //
        // EMAIL VERIFICATION
        //



        $status = Password::reset(
            ['email' => $user->email, 'password' => $request->password,  'token' => $request->fingerprint],
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                event(new PasswordReset($user));
            }
        );



        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {

            $role = $user->getRoleNames()->first();
            $token = $user->createToken(abilities: [$role]);
            $data = [
                "token" => $token->plainTextToken,
                "user" => new UserResource($user),
                "role" => $role,
            ];

            return YResponse::json(data: $data, status: 201);

            return YResponse::json(__('auth.reset_password_success'), data: $data);
        }

        throw ValidationException::withMessages([
            'fingerprint' => [$status ? trans($status) : __('passwords.token')],
        ]);
    }
}
