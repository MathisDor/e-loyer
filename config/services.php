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

    /*
    |--------------------------------------------------------------------------
    | Mobile Money Services - Gabon
    |--------------------------------------------------------------------------
    */

    'airtel' => [
        'client_id' => env('AIRTEL_CLIENT_ID'),
        'client_secret' => env('AIRTEL_CLIENT_SECRET'),
        'api_url' => env('AIRTEL_API_URL', 'https://openapiuat.airtel.africa'),
        'callback_url' => env('AIRTEL_CALLBACK_URL'),
    ],

    'moov' => [
        'api_key' => env('MOOV_API_KEY'),
        'api_secret' => env('MOOV_API_SECRET'),
        'api_url' => env('MOOV_API_URL'),
        'callback_url' => env('MOOV_CALLBACK_URL'),
    ],

    'gabon_telecom' => [
        'api_key' => env('GABON_TELECOM_API_KEY'),
        'api_url' => env('GABON_TELECOM_API_URL'),
        'callback_url' => env('GABON_TELECOM_CALLBACK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps
    |--------------------------------------------------------------------------
    */

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Service
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'api_key' => env('SMS_API_KEY'),
        'api_secret' => env('SMS_API_SECRET'),
        'sender_id' => env('SMS_SENDER_ID', 'E-Loyer'),
    ],

];
