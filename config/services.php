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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Proveedor de IA del consultor: Groq con modelos propios openai/gpt-oss-*.
    // Para cambiar el modelo, añade GROQ_MODELO en .env.
    // Modelos disponibles en esta cuenta: openai/gpt-oss-20b, openai/gpt-oss-120b
    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'modelo'  => env('GROQ_MODELO', 'openai/gpt-oss-20b'),
        'url'     => 'https://api.groq.com/openai/v1/chat/completions',
    ],

];
