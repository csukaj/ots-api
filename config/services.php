<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => Modules\Stylersauth\Entities\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'channel_managers' => [
        'log_file' => storage_path('logs/availability.log'),
        'providers' => [
            'hotel_link_solutions' => [
                'wsdl_url' => env('HOTEL_LINK_SOLUTIONS_WSDL_URL',
                    'http://hbe-api.whl-staging.com/services/inventory/soap?wsdl'),
                'credential' => [
                    'username' => env('HOTEL_LINK_SOLUTIONS_API_USERNAME', 'ota'),
                    'password' => env('HOTEL_LINK_SOLUTIONS_API_PASSWORD', 'ota')
                ],
                'availability_to_days' => 548 //1.5 year
            ]
        ]
    ],
];
