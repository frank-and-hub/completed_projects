<?php

namespace App\Http\Controllers\Api;

use App\Helpers\YResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate(onlyRoles: ['user']);

        $user = $request->user();

        if ($user->is_active == false) {
            return YResponse::json(__('api_message.account_deactivated'), status: 400);
        }


        $role = $user->getRoleNames()->first();
        $token = $user->createToken(abilities: [$role]);

        $data = [
            "token" => $token->plainTextToken,
            "user" => new UserResource($user->fresh()),
            "role" => $role,
        ];

        return YResponse::json(data: $data);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!empty($user)) {
            if ($user->is_active == false) {
                return YResponse::json(__('api_message.account_deactivated'), status: 400);
            }
        }

        if (!empty($user)) {
            return YResponse::json(__('api_message.your_account_already_exists'), status: 400);
        }
        try {

            DB::beginTransaction();

            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username'=>$request->username,
                'password' => Hash::make($request->password),
                'is_active' => 1
            ]);

            $user->assignRole('user');
            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();

            return YResponse::json(message: $e->getMessage(), status: 404);
        }

        $user->fresh();

        $user->sendEmailVerificationNotification();

        $role = $user->getRoleNames()->first();
        $token = $user->createToken(abilities: ['verify-email']);

        $data = [
            "token" => $token->plainTextToken,
            "user" => new UserResource($user->fresh()),
            "role" => $role,
        ];

        return YResponse::json(data: $data, status: 201);
    }

    public function send_email_verification(Request $request): JsonResponse
    {
        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            throw $e;
        }

        return YResponse::json(__('auth.email_sent'));
    }

    public function verify_email(Request $request): JsonResponse
    {
        $request->validate([
            "otp" => "required|min:4",
        ]);

        $user = $request->user();

        $verification = $user->verifications()->where([
            "scope" => "verification",
            "verification_type" => "email",
            "status" => "pending",
        ])->where("valid_upto", ">", now())->where("verifying", $user->email)
            ->first();

        if (empty($verification)) {
            return YResponse::json(__('auth.otp_expired'), status: 400);
        }

        if ($verification->otp != $request->otp) {
            return YResponse::json(__('auth.otp_invalid'), status: 400);
        }

        if ($user->verifyEmailOtp(otp: $request->otp)) {

            $role = $user->getRoleNames()->first();
            $token = $user->createToken(abilities: [$role]);

            $data = [
                "token" => $token->plainTextToken,
                "user" => new UserResource($user->fresh()),
                "role" => $user->getRoleNames()->first(),
            ];

            return YResponse::json(message: __('auth.email_verified_success'), data: $data);
        }

        return YResponse::json(__('auth.otp_not_matched'), status: 400);
    }

    public function change_password(Request $request)
    {
        $request->validate([
            'current_password' => ['sometimes', 'string', 'min:8'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->numbers()->mixedCase()],
        ]);

        $user = $request->user();

        if ((Hash::check(request('current_password'), $user->password)) == false && $user->password != null) {
            return YResponse::json(message: __('admin.invalid_current_password'), status: 406);
        }

        if ((Hash::check(request('password'), $user->password)) == true) {
            return YResponse::json(message: __('admin.invalid_new_password'), status: 406);
        }

        $user = User::find($user->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return YResponse::json(message: __('admin.password_change_success'), status: 200);
    }

    public function signout(Request $request): JsonResponse
    {
        if ($request->all_devices) {
            $request->user()->tokens()->delete();
        } else {
            $request->user()->currentAccessToken()->delete();
        }

        return YResponse::json(message: __("auth.signout_success"));
    }

    public function delete(Request $request)
    {
        $user = $request->user();

        $user = User::find($user->id);
        $user->is_active = false;
        $user->save();

        $request->user()->tokens()->delete();

        return YResponse::json(message: __("auth.account_deleted_success"));
    }
}
