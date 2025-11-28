<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'package' => [
        'name' => env('PACKAGE_NAME')
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
        'apple_client_id' => env('GOOGLE_APPLE_CLIENT_ID'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI'),
    ],

    'apple' => [
        'client_id'     => env('APPLE_CLIENT_ID'),
        'client_secret' => env('APPLE_CLIENT_SECRET'),
        'redirect'      => env('APPLE_REDIRECT_URI'),
    ],

    'aws' => [
        'bucket' => env('AWS_BUCKET')
    ],

    'subscription' => [
        'package_name' => env('PACKAGE_NAME'),
    ],
    
    'google_play' => [
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'package_name' => env('GOOGLE_PLAY_PACKAGE'),
    ],
    
    'apple_pay' => [
        'shared_secret' => env('APPLE_SHARED_SECRET'),
        'in_app_env' => env('APPLE_ENV', 'sandbox'), // sandbox or production
        'key_id' => env('APPLE_KEY_ID'),
        'issuer_id' => env('APPLE_ISSUER_ID'),
        'bundle_id' => env('APPLE_BUNDLE_ID')
    ],

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'project_id' => env('FCM_PROJECT_ID'),
        'base_url'   => env('FCM_BASE_URL'),
    ],
];
