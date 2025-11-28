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

    'aws' => [
        's3' => [
            'bucket' => env('AWS_BUCKET'),
            'access_key' => env('AWS_BUCKET_ACCESS_KEY_ID'),
            'secret_access_key' => env('AWS_BUCKET_ACCESS_SECRET_ACCESS_KEY'),
        ],
        'face_liveness' => [
            'access_key' => env('AWS_FACElIVENESS_ACCESS_KEY_ID'),
            'secret_access_key' => env('AWS_FACElIVENESS_SECRET_ACCESS_KEY'),
            'region' => 'eu-west-1',
        ],
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'outlook' => [
        'client_id' => env('OUTLOOK_CLIENT_ID'),
        'client_secret' => env('OUTLOOK_CLIENT_SECRET'),
        'redirect' => env('OUTLOOK_REDIRECT_URI'),
    ],
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect' => env('MICROSOFT_REDIRECT_URI'),
    ],

    'recaptcha' => [
        'site_key' => env('SITE_KEY'),
        'secret_key' => env('SECRET_KEY'),
    ],
    'onesignal' => [
        'app_id' => env('ONESIGNAL_APPID'),
        'api_key' => env('ONESIGNAL_APIKEY'),
    ],
    'payfast' => [
        'passphrase' => env('PAYFAST_PASSPHRASE'),
        'merchant_id' => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'payfast_url' => env('PAYFAST_URL'),
        'payfast_mode' => env('PAYFAST_MODE'),
        'testing' => env('PAYFAST_TESTING', true),
    ],
    'property' => [
        'tenant_request_per_payment' => env('TENANT_REQUEST_PER_PAYMENT', 5),
    ],
    'whatsapp' => [
        'phone_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'auth_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'business_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'template' => [
            'basic' => env('BASIC_TEMPLATE'),
            'professional' => env('PROFESSIONAL_TEMPLATE'),
            'event' => env('EVENT_TEMPLATE'),
            'contract' => env('CONTRACT_TEMPLATE'),
            'agent_property_matched' => env('AGENT_PROPERTY_MATCHED_TEMPLATE'),
            'tenant_contract_message' => env('TENANT_CONTRACT_MESSAGE'),
            'property_reschedule' => env('PROPERTY_RESCHEDULED'),
        ],
        'welcome_template' => [
            'agency' => env('AGENCYWELCOME_TEMPLATE'),
            'agent' => env('AGENTWELCOME_TEMPLATE'),
            'landlord' => env('LANDLORDWELCOME_TEMPLATE'),
            'tenant' => env('TENANTWELCOME_TEMPLATE'),
        ]
    ],

    'entegral' => [
        'username' => env('ENTEGRAL_USERNAME', null),
        'password' => env('ENTEGRAL_PASSWORD', null),
        'entegral_url' => env("ENTEGRAL_URL", ''),
        'entegral_office_url' => env("ENTEGRAL_OFFICE_URL", ''),
        'entegral_area_url' => env("ENTEGRAL_AREA_URL", ''),
    ],

    'agentemail' => env('AGENTEMAIL', ''),

    'getVerified' => [
        'api_key' => env('CREDIT_API_KEY'),
        'webhook_secret' => env('CREDIT_VERIFIED_SECRET_KEY'),
        'gov_site' => env('CREDIT_GOV_SITE'),
    ],

    'live' => env('LIVE',0),
];
