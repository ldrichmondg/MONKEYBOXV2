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

    'parcelsapp' => [
        'api_key' => env('PARCELSAPP_API_KEY'),
        'url_seguimiento' => env('PARCELSAPP_URL_SEGUIMIENTO'),
    ],

    'aeropost' => [
        'grant_type' => env('AEROPOST_GRANTTYPE'),
        'scope' =>  env('AEROPOST_SCOPE'),
        'username' => env('AEROPOST_USERNAME'),
        'password' => env('AEROPOST_PASSWORD'),
        'gateway' => env('AEROPOST_GATEWAY'),
        'url_auth' => env('AEROPOST_URL_AUTH'),
        'url_base' =>  env('AEROPOST_URL_BASE'),
        'client_id' => env('AEROPOST_CLIENT_ID'),
        'client_secret' => env('AEROPOST_CLIENT_SECRET'),
    ],

    'aeropostDev' => [
        'grant_type' => env('AEROPOST_GRANTTYPE'),
        'scope' =>  env('AEROPOST_SCOPE'),
        'username' => env('AEROPOST_USERNAME_DEV'),
        'password' => env('AEROPOST_PASSWORD_DEV'),
        'gateway' => env('AEROPOST_GATEWAY'),
        'url_auth' => env('AEROPOST_URL_AUTH_DEV'),
        'url_base' =>  env('AEROPOST_URL_BASE_DEV'),
        'client_id' => env('AEROPOST_CLIENT_ID_DEV'),
        'client_secret' => env('AEROPOST_CLIENT_SECRET_DEV'),
    ],

];
