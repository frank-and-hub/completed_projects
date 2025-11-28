<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected string $fcmUrl;
    protected string $serverKey;
    protected string $oauthUrl;
    public function __construct()
    {
        $this->fcmUrl   = config('services.fcm.base_url');
        $this->serverKey = config('services.fcm.server_key');
        $this->oauthUrl = 'https://oauth2.googleapis.com/token';
    }
    public function create(
        int $receiverId,
        string $type,
        string $title,
        string $message,
        ?string $thumbnail = null,
        array $meta = []
    ): bool|Notification {
        try {
            $receiver = User::find($receiverId);
            if (!$receiver) {
                Log::warning("NotificationService::create - User not found: {$receiverId}");
                return false;
            }
            $meta = array_merge($meta, [
                'notification_type' => $type,
                'receiver_id'       => (string) $receiver->id,
            ]);
            $notification = Notification::create([
                'user_id'   => $receiver->id,
                'type'      => $type,
                'title'     => $title,
                'message'   => $message,
                'meta'      => $meta,
                'thumbnail' => $thumbnail,
            ]);
            $notification->update([
                'meta' => array_merge($notification->meta ?? [], [
                    'notification_id' => (string) $notification->id,
                ]),
            ]);
            if ($receiver->device_id && $receiver->notifications_enabled) {
                $this->sendPush($receiver->device_id, $title, $message, $notification->meta);
            }
            return $notification;
        } catch (\Throwable $e) {
            Log::error("NotificationService::create error: {$e->getMessage()}");
            return false;
        }
    }
    public function sendPush(?string $deviceToken, string $title, string $body, array $data = []): ?array
    {
        if (!$deviceToken) {
            Log::warning('NotificationService::sendPush - Missing device token');
            return null;
        }
        try {
            $payload = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $data,
                ],
            ];
            $accessToken = $this->getAccessToken();
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])
                ->withOptions(['verify' => false])
                ->post($this->fcmUrl, $payload)
                ->json();

            Log::info('\n accessToken:' . $accessToken);
            Log::info('\n FCM Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));
            Log::info('\n FCM Response: ' . json_encode($response, JSON_PRETTY_PRINT) . '\n');

            return $response;
        } catch (\Throwable $e) {
            // Log::error("NotificationService::sendPush error: {$e->getMessage()}");
            return null;
        }
    }
    protected function getAccessToken(): string
    {
        return Cache::remember('firebase_access_token', 55 * 60, function () {
            $keyPath = storage_path('app/notification/service.json');
            if (!file_exists($keyPath)) {
                throw new \Exception("Firebase credentials file not found at {$keyPath}");
            }
            $credentials = json_decode(file_get_contents($keyPath), true);
            $data = [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $this->generateJwtAssertion($credentials),
            ];
            $response = Http::asForm()
                ->withOptions(['verify' => false])
                ->post($this->oauthUrl, $data)
                ->json();
            if (!isset($response['access_token'])) {
                throw new \Exception('Unable to retrieve Firebase access token: ' . json_encode($response));
            }
            return $response['access_token'];
        });
    }
    protected function generateJwtAssertion(array $credentials): string
    {
        $now = time();
        $expires = $now + 3600;
        $jwtHeader = ['alg' => 'RS256', 'typ' => 'JWT'];
        $jwtPayload = [
            'iss'   => $credentials['client_email'],
            'sub'   => $credentials['client_email'],
            'aud'   => $this->oauthUrl,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'iat'   => $now,
            'exp'   => $expires,
        ];
        $encodedHeader  = rtrim(strtr(base64_encode(json_encode($jwtHeader)), '+/', '-_'), '=');
        $encodedPayload = rtrim(strtr(base64_encode(json_encode($jwtPayload)), '+/', '-_'), '=');
        $dataToSign = $encodedHeader . '.' . $encodedPayload;
        openssl_sign($dataToSign, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
        $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "{$dataToSign}.{$encodedSignature}";
    }
    public function sendWorkoutReminder(int $userId): bool|Notification
    {
        return $this->create(
            receiverId: $userId,
            type: 'workout_reminder',
            title: 'Workout Reminder',
            message: "Don’t forget! Today is your training day. Ready to crush it?",
            meta: ['category' => 'workout'],
        );
    }
    public function sendProgressUpdate(int $userId, int $percentCompleted = 80): bool|Notification
    {
        if ($percentCompleted < 50) {
            $message = "You're getting started! Keep going — every step counts.";
        } elseif ($percentCompleted >= 50 && $percentCompleted < 60) {
            $message = "Good effort! You’ve completed over half your workouts. Keep pushing!";
        } elseif ($percentCompleted >= 60 && $percentCompleted < 70) {
            $message = "Nice work! You’re building great consistency — stay strong!";
        } elseif ($percentCompleted >= 70 && $percentCompleted < 80) {
            $message = "Great progress! You’ve completed most of your workouts this week!";
        } elseif ($percentCompleted >= 80 && $percentCompleted < 90) {
            $message = "Awesome job! You’re almost at your goal — keep the momentum!";
        } elseif ($percentCompleted >= 90 && $percentCompleted < 100) {
            $message = "Fantastic! You’re just a step away from completing all your workouts!";
        } else {
            $message = "Outstanding! You’ve completed all your workouts this week — amazing dedication!";
        }
        return $this->create(
            receiverId: $userId,
            type: 'progress_update',
            title: 'Progress Update',
            message: $message,
            meta: [
                'category' => 'progress',
                'percentCompleted' => (string) $percentCompleted,
            ],
        );
    }
    public function sendMilestoneAlert(int $userId, int $workoutCount = 50): bool|Notification
    {
        return $this->create(
            receiverId: $userId,
            type: 'milestone_alert',
            title: 'Milestone Alert',
            message: "You just logged your {$workoutCount}th workout! Keep pushing forward.",
            meta: [
                'category' => 'milestone',
                'workoutCount' => (string) $workoutCount,
            ],
        );
    }
    public function sendDailyMotivationalBoost(int $userId): bool|Notification
    {
        $user = User::find($userId);
        $firstName = $user ? explode(' ', trim($user->fullname))[0] ?? 'Athlete' : 'Athlete';
        $title = 'Motivational Boost';
        $messages = [
            "You’ve been crushing it with consistent workouts, :name! Your dedication is inspiring—don’t stop now!",
            "Amazing job, :name! You just achieved a new personal best. Every rep counts—stay consistent and keep challenging yourself!",
            "Congratulations, :name! You’ve just hit a new milestone in your fitness journey. Keep pushing forward—your hard work is paying off!",
            "You’re unstoppable, :name! Every session gets you one step closer to your goals—keep going strong!",
            "Another milestone crushed, :name! Your consistency is what sets you apart—stay on track!",
            "Way to go, :name! You’ve proven that commitment beats excuses every time. Keep showing up!",
            "Your progress speaks for itself, :name—stronger, faster, and more focused every day!",
            "You’re leveling up, :name! Keep this momentum alive and smash your next goal!",
            "Consistency wins again, :name! You’re building habits that last a lifetime.",
            "Look at you go, :name! Each workout is shaping a stronger, more confident version of you.",
            "Another goal achieved, :name! Your dedication is truly inspiring. Keep the fire alive!",
            "Your hard work is paying off, :name! Stay consistent, and the results will keep coming."
        ];
        $message = str_replace(':name', $firstName, $messages[array_rand($messages)]);
        return $this->create(
            receiverId: $userId,
            type: 'daily_motivation',
            title: $title,
            message: $message,
            meta: ['category' => 'motivation'],
        );
    }
}
