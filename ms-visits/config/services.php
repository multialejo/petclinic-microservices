<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Secreto compartido para llamadas servicio -> servicio (cabecera X-Internal-Token).
    // Se inyecta desde el .env de la raíz vía docker-compose (una sola fuente para los 3 servicios).
    'internal' => [
        'token' => env('INTERNAL_API_TOKEN'),
    ],

    // ms-customers: validación síncrona del pet al crear una visita.
    'customers' => [
        'url' => env('CUSTOMERS_URL', 'http://ms-customers'),
        'token' => env('INTERNAL_API_TOKEN'),
    ],

];
