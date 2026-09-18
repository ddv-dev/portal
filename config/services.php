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

    'ldap' => [
        'host'     => env('LDAP_HOST'),
        'base_dn'  => env('LDAP_BASE_DN'),
        'username' => env('LDAP_USERNAME'),
        'password' => env('LDAP_PASSWORD'),
    ],
    'redmine' => [
        'url'      => env('REDMINE_URL', 'http://m01-redmine-01.m01.dzm'),
        'api_key'  => env('REDMINE_API_KEY'),
            'files_path' => env('REDMINE_FILES_PATH', '/var/www/redmine/files'),

    ],

];
