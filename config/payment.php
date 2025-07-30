<?php

return [
    'log' => storage_path('logs/payment.log'),
    'limonetik' => [
        'API_KEY' => env('LIMONETIK_API_KEY'),
        'SALT_KEY' => env('LIMONETIK_SALT_KEY'),
        'ENV' => env('LIMONETIK_ENV'),
        # @todo @ivan ez lett a technical merchentID, ezt majd vegig kell vezetni, hogy a nevezektan jo legyen!
        'MERCHANT_ID' => env('LIMONETIK_TECHNICAL_MERCHANT_ID'),
        'PAYMENT_PAGE_ID' => env('LIMONETIK_PAYMENT_PAGE_ID'),
        'SITE_URL' => env('FRONTEND_URL'),
        'API_URL' => env('API_URL'),
        'MERCHANT_URLS' => [
            'en' => [
                'returnUrl' => '/payment-checking',
                'abortedUrl' => '/payment-aborted',
                'errorUrl' => '/payment-failed',
            ],
            'hu' => [
                'returnUrl' => '/fizetes-ellenorzese',
                'abortedUrl' => '/fizetes-megszakitva',
                'errorUrl' => '/sikertelen-fizetes',
            ],
            'de' => [
                'returnUrl' => '/payment-checking',
                'abortedUrl' => '/payment-aborted',
                'errorUrl' => '/payment-failed',
            ],
            'ru' => [
                'returnUrl' => '/payment-checking',
                'abortedUrl' => '/payment-aborted',
                'errorUrl' => '/payment-failed',
            ],
        ]
    ]
];