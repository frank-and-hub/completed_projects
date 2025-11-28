<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\{Common_trait, UserWorkoutCloner};
use Illuminate\Support\Facades\{Http, DB, Log, Validator};
use Symfony\Component\HttpKernel\Exception\HttpException;

class SocialLoginController extends Controller
{
    use Common_trait, UserWorkoutCloner;

    public function socialLogin(Request $req)
    {
        $v = Validator::make($req->all(), [
            'socialId' => 'required|string',
            'socialType' => 'required|in:1,2,3',
            'email' => 'required|email',
            'deviceToken' => 'required|string',
            'deviceType' => 'required',
            'fullname' => 'nullable|string',
            'access_token' => 'nullable|string'
        ]);
        $loginType = [
            1 => 2, // Google
            2 => 3, // Apple,
            3 => 4, // facebook
        ];
        if ($v->fails()) {
            return ApiResponse::error($v->errors()->first(), 422);
        }
        Log::info("Social login attempt: \n");
        Log::info(json_encode($req->all()));
        $userData = [];
        DB::beginTransaction();
        try {
            switch ($req->socialType) {
                case 1:
                    $userData = $this->verifyGoogleToken($req->socialId, $req->socialId);
                    break;
                case 2:
                    $userData = $this->verifyAppleToken($req->socialId, $req->deviceToken);
                    break;
                case 3:
                    $userData = $this->verifyFacebookToken($req->access_token, $req->only('socialId', 'fullname', 'email'), $req->deviceType);
                    break;
                default:
                    DB::rollBack();
                    return ApiResponse::error('Unsupported social type', 401);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in social login: " . $e->getMessage() . ' - ' . $e->getLine());
            return ApiResponse::error('Login failed, Please try again.', 401);
        }
        if (empty($userData) && !isset($userData['social_id'])) {
            return ApiResponse::error('Invalid token, Please try again.', 401);
        }
        Log::info('Social login user data: ');
        Log::info(print_r($userData, true));
        $socialId  = $userData['social_id'] ?? ($req->socialId ?? $req->deviceToken);
        $email = $userData['email'] ?? $req->email ?? null;
        $user = User::when($req->deviceToken, fn($q) => $q->where('device_id', $req->deviceToken))
            ->where('social_id', $socialId)
            ->first();
        if (!$user) {
            if (!$email) {
                $email = "user_{$socialId}@apple.local";
            }
        }
        $user = User::updateOrCreate(
            [
                'email' => $email,
                'social_id' => $socialId,
            ],
            [
                'login_type'  => $loginType[$req->socialType],
                'device_type' => $req->deviceType,
                'device_id'   => $req->deviceToken,
            ]
        );
        if (!empty($userData['picture']) &&  $user->profile_photo == null) {
            try {
                $imageContents = file_get_contents($userData['picture']);
                $tempFile = tempnam(sys_get_temp_dir(), 'profile_') . '.jpg';
                file_put_contents($tempFile, $imageContents);
                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tempFile,
                    basename($tempFile),
                    'image/jpeg',
                    null,
                    true
                );
                $userProfilePhotoPath = $this->file_upload(
                    $uploadedFile,
                    config('constants.uploads') . '/' . $user->id . '/' . config('constants.user_profile_photo')
                );
                $user->update(['profile_photo' => is_array($userProfilePhotoPath) ? ($userProfilePhotoPath['picture'] ?? $userProfilePhotoPath['original']) : $userProfilePhotoPath]);
            } catch (\Exception $e) {
                Log::error("Failed to download user profile image: " . $e->getMessage());
            }
        }
        if ($user->wasRecentlyCreated) {
            $user->role = 2;
            $user->save();
        } else {
            $user?->currentAccessToken()?->delete();
            $user?->tokens()?->delete();
            if ($user->status == 0) {
                DB::rollBack();
                return ApiResponse::error(__('messages.deactivate_account'), 403);
            }
        }
        $token = $user->createToken(config('app.api.secret_key'))->plainTextToken;
        $lastToken = $user->tokens()?->latest()->first();
        if ($lastToken) {
            $user->last_token_id = $lastToken->id;
            $user->save();
        }
        $newUser = User::with('work_out_frequency')->find($user->id);
        DB::commit();
        return ApiResponse::success(['user' => new UserResource($newUser), 'token' => $token], __('messages.login'));
    }

    private function verifyGoogleToken($idToken, $deviceToken)
    {
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}";
        try {
            Log::info('Google token verification: ' . $url);
            $validClientIds = [
                config('services.google.client_id'),
                config('services.google.apple_client_id')
            ];
            $response = Http::withoutVerifying()->get($url);
            if ($response->successful()) {
                $data = $response->json();
                Log::info("verifyGoogleToken : ");
                Log::info($data);
                if (!in_array(($data['aud'] ?? null), $validClientIds)) {
                    throw new HttpException(401, 'Invalid Google client ID.');
                }
                return [
                    'email' => $data['email'] ?? null,
                    'name'  => $data['name'] ?? null,
                    'picture'  => $data['picture'] ?? null,
                    'social_id' => $data['sub'] ?? $deviceToken,
                ];
            }
            throw new HttpException(401, 'Login failed, Please try again.');
        } catch (\Exception $e) {
            Log::info('Google token verification failed check Log : ' . $e->getMessage() . ' - Line:' . $e->getLine() . ' - URL: ' . $url);
            throw new HttpException(401, 'Login failed, Please try again.');
        }
    }

    private function verifyAppleToken($identityToken, $social_id)
    {
        try {
            $payload = explode('.', $identityToken);
            if (count($payload) < 2) return null;
            $claims = json_decode(base64_decode($payload[1]), true);
            Log::info("verifyAppleToken : " . config('services.apple.client_id'));
            Log::info($claims);
            if (($claims['aud'] ?? null) !== config('services.apple.client_id')) {
                throw new HttpException(401, 'Invalid Apple client ID.');
            }
            return [
                'email' => $claims['email'] ?? null,
                'social_id' => $claims['sub'] ?? $social_id ?? null,
                'name'  => null,
                'picture' => null
            ];
        } catch (\Exception $e) {
            throw new HttpException(401, 'Login failed, Please try again.');
        }
    }

    private function verifyFacebookToken($userAccessToken, $data, $deviceType)
    {
        try {
            if ($deviceType == 'ios') {
                $payload = explode('.', $userAccessToken);
                if (count($payload) < 2) return null;
                $claims = json_decode(base64_decode($payload[1]), true);
                return [
                    'email' => $claims['email'] ?? null,
                    'social_id' => $data['social_id'] ?? null,
                    'name'  => $claims['given_name'] ?? null,
                    'picture' => $claims['picture'] ?? null,
                ];
            }
            $url = "https://graph.facebook.com/me?fields=id,name,email,picture&access_token={$userAccessToken}";
            $userResponse = Http::withoutVerifying()->get($url);
            if ($userResponse->failed()) {
                throw new HttpException(401, 'Failed to fetch user info from Facebook.');
            }
            $user = $userResponse->json();
            return [
                'email'     => $user['email'] ?? null,
                'name'      => $user['name'] ?? null,
                'picture'   => $user['picture']['data']['url'] ?? null,
                'social_id' => $data['social_id'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Facebook token verification failed: ' . $e->getMessage() . ' - ' . $e->getLine());
            throw new HttpException(401, 'Facebook login failed. Please try again.');
        }
    }
}
