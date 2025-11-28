<?php

use App\Models\User;
use App\Notifications\MealReminderNotification;
use App\Notifications\WorkoutReminderNotification;
use App\Notifications\ChallengeReminderNotification;
// log 
use Illuminate\Support\Facades\Log;

if (!function_exists('pre')) {
    function pre($data = '', $status = FALSE)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if (!$status) {
            die;
        }
    }
}

// For API
if (!function_exists('pree')) {
    function pree($data = '', $status = FALSE)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        if (!$status) {
            die;
        }
    }
}

if (!function_exists('getDateInFormat')) {
    function getDateInFormat($date)
    {
        if (!empty($date)) {
            $dateTimeObject = new DateTime($date);
            return $formattedDateTime = $dateTimeObject->format('d M, Y');
        } else {
            return '-';
        }
    }
}


if (!function_exists('get_avatar')) {
    function get_avatar($avatar = '')
    {
        return $avatar == '' ? asset('assets/img/user.png') : asset($avatar);
    }
}



if (!function_exists('deleteFromS3')) {
    function deleteFromS3($filePath)
    {
        if (!$filePath) {
            return false;
        }

        try {
            return Storage::disk('s3')->delete($filePath);
        } catch (\Exception $e) {
            app('log')->error('AWS S3 Deletion Error: ' . $e->getMessage());
            return false;
        }
    }

    if (!function_exists('typeLabels')) {
        function typeLabels()
        {
            return [
                1 => 'Breakfast',
                2 => 'Lunch',
                3 => 'Dinner',
            ];
        }
    }

    // function notifyUser($item)
    // {
    //     Log::info('notifyUser called', $item);
    //     $key = getAccessToken();

    //     $url = 'https://fcm.googleapis.com/v1/projects/fitpro360-fdaf8/messages:send';

    //     $type = $item['type'] ?? 'general';

    //     switch ($type) {
    //         case 'is_meal':
    //             $title = 'Meal Reminder';
    //             $body = $item['message'] ?? 'It’s mealtime! Check your plan and stay fueled for success!';
    //             break;
    //         case 'is_workout':
    //             $title = 'Workout Reminder';
    //             $body = $item['message'] ?? 'Time to move! Complete today’s workout and stay on track with your fitness goals!';
    //             break;
    //         default:
    //             $title = $item['item'] ?? 'Challenge Update';
    //             $body = $item['message'] ?? 'New challenge unlocked! Join now and level up your fitness journey!';
    //             break;
    //     }

    //     $fields = [
    //         "message" => [
    //             "token" => $item['deviceToken'],
    //             "notification" => [
    //                 "title" => $title,
    //                 "body"  => $body,
    //             ],
    //             "data" => [
    //                 "type" => (string) $type,
    //                 "workout_id" => isset($item['id']) ? (string) $item['id'] : '',
    //                 "challenge_id" => isset($item['id']) ? (string) $item['id'] : '',
    //                 "meal_id" => isset($item['meal_id']) ? (string) $item['meal_id'] : '0',
    //                 "user_id" => isset($item['user_id']) ? (string) $item['user_id'] : '',
    //                 "notification_type" => (string) $type,
    //             ],
    //         ]
    //     ];

    //     $headers = [
    //         'Authorization: Bearer ' . $key,
    //         'Content-Type: application/json',
    //     ];

    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    //     $result = curl_exec($ch);
    //     curl_close($ch);

    //     if (!empty($item['user_id'])) {
    //         $user = User::find($item['user_id']);
    //         if ($user) {
    //             $notificationData = [
    //                 'title' => $title,
    //                 'message' => $body,
    //                 'type' => $type,
    //                 'id' => $item['id'] ?? null,
    //             ];

    //             if ($type === 'is_workout') {
    //                 $user->notify(new WorkoutReminderNotification($notificationData));
    //             } elseif ($type === 'is_meal') {
    //                 $user->notify(new MealReminderNotification($notificationData));
    //             } elseif ($type === 'is_challenge') {
    //                 $user->notify(new ChallengeReminderNotification($notificationData));
    //             }
    //             Log::info('notifyUser called for user_id: ' . ($item['user_id'] ?? 'unknown'));
    //             Log::info("FCM sent + Notification saved for user: " . $item['user_id']);
    //         }
    //     }

    //     $data = json_decode($result, true);
    //     return $data;
    // }

    function notifyUser($item)
    {
        // Get the user
        $user = User::find($item['user_id']);
        if (!$user) {
            Log::warning('Unauthorized: User not found for notification.');
            return false;
        }

        if (!$user->notifications_enabled) {
            Log::warning("Notifications are disabled for user {$user->id}.");
            return false;
        }

        Log::info('notifyUser called', $item);

        // Firebase Cloud Messaging (FCM) access token
        $key = getAccessToken();
        $url = 'https://fcm.googleapis.com/v1/projects/fitpro360-fdaf8/messages:send';
        $type = $item['type'] ?? 'general';

        // Determine title and message
        switch ($type) {
            case 'is_meal':
                $title = $item['title'] ?? 'Meal Alerts';
                $body  = $item['message'] ?? 'It’s mealtime! Check your plan and stay fueled for success!';
                break;

            case 'is_workout':
                $title = $item['title'] ?? 'Daily Workout Reminder';
                $body  = $item['message'] ?? 'Time to move! Complete today’s workout and stay on track with your fitness goals!';
                break;

            case 'is_challenge':
                $title = $item['title'] ?? 'New Challenge🔥';
                $body  = $item['message'] ?? 'A fresh fitness challenge just dropped. Accept it, crush it, brag later.';
                break;

            default:
                $title = $item['title'] ?? ($item['item'] ?? 'Notification');
                $body  = $item['message'] ?? 'You have a new update.';
                break;
        }

        // Prepare FCM payload
        $fields = [
            "message" => [
                "token" => $item['deviceToken'],
                "notification" => [
                    "title" => $title,
                    "body"  => $body,
                ],
                "android" => [
                    "notification" => [
                        "sound" => "default", 
                    ],
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "default", 
                        ],
                    ],
                ],
                "data" => [
                    "type"              => (string) $type,
                    "workout_id"        => (string) ($item['workout_id'] ?? ''),
                    "challenge_id"      => (string) ($item['challenge_id'] ?? ''),
                    "meal_id"           => (string) ($item['meal_id'] ?? '0'),
                    "user_id"           => (string) ($item['user_id'] ?? ''),
                    "notification_type" => (string) $type,
                ],
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $key,
            'Content-Type: application/json',
        ];

        // Send FCM push notification
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        $result = curl_exec($ch);
        curl_close($ch);

        // Laravel DB Notification
        if (!empty($item['user_id']) && $user) {
            switch ($type) {
                case 'is_workout':
                    $user->notify(new \App\Notifications\WorkoutReminderNotification([
                        'title'   => $title,
                        'message' => $body,
                        'type'    => $type,
                        'id'      => $item['id'] ?? null,
                    ]));
                    break;

                case 'is_meal':
                    $user->notify(new \App\Notifications\MealReminderNotification([
                        'title'   => $title,
                        'message' => $body,
                        'type'    => $type,
                        'id'      => $item['id'] ?? null,
                    ]));
                    break;

                case 'is_challenge':
                    $user->notify(new \App\Notifications\ChallengeReminderNotification([
                        'title'   => $title,
                        'message' => $body,
                        'type'    => $type,
                        'id'      => $item['id'] ?? null,
                    ]));
                    break;

                default:
                    // Handle other types if needed
                    break;
            }

            Log::info("DB notification sent to user_id: {$item['user_id']} (type: {$type})");
        }

        // Parse FCM response
        $data = json_decode($result, true);
        Log::info('FCM response:', is_array($data) ? $data : ['response' => $data]);

        Log::info('FCM response:', (array) $data);


        // Handle invalid FCM token
        if (
            isset($data['error']['status']) &&
            in_array($data['error']['status'], ['INVALID_ARGUMENT', 'NOT_FOUND'])
        ) {

            Log::warning("Invalid FCM token for user {$item['user_id']}: {$item['deviceToken']}");

            if ($user) {
                $user->device_id = null;
                $user->save();
                Log::info("Cleared invalid device_token for user {$item['user_id']}");
            }
        }

        return $data;
    }


    function getAccessToken()
    {
        $url = 'https://oauth2.googleapis.com/token';
        $key = base_path('storage/app/notification/fitpro360-fdaf8-3a4350986cee.json');
        // $key = __DIR__ ;
        //  pree($key);

        $credentials = json_decode(file_get_contents($key), true);
        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => generateJwtAssertion($credentials),
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        $tokenData = json_decode($result, true);
        //pree($result);
        return $tokenData['access_token'];
    }

    function generateJwtAssertion($credentials)
    {
        $now = time();
        $expires = $now + 3600; // Token expires in one hour

        $jwtHeader = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $jwtPayload = [
            'iss' => $credentials['client_email'],
            'sub' => $credentials['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'iat' => $now,
            'exp' => $expires,
        ];

        $encodedHeader = base64_encode(json_encode($jwtHeader));
        $encodedPayload = base64_encode(json_encode($jwtPayload));

        $dataToSign = $encodedHeader . '.' . $encodedPayload;
        $signature = '';
        openssl_sign($dataToSign, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
        $encodedSignature = base64_encode($signature);

        return $dataToSign . '.' . $encodedSignature;
    }
}
