<?php

return [
    'log' => storage_path('logs/billing.log'),
    'szamlazzhu' => [
        'currency' => 'EUR',
        'vat' => '0',
        'seller' => [
            "email_replyto" => env('SZAMLAZZHU_REPLY_EMAIL')
        ],
        'beallitasok' => [
            'username' => env('SZAMLAZZHU_USERNAME'),
            'password' => env('SZAMLAZZHU_PASSWORD'),
        ],
        'defaults' => [
            'log_level' => 3,
            'log' => storage_path('logs/szamlazzhu.log'),
            'log_email' => env('SZAMLAZZHU_LOG_EMAIL'),
            'return_pdf_as_result' => false,
            'pdf_file_save_path' => storage_path('szamlazzhu/invoices/'),
            'xml_file_save_path' => storage_path('szamlazzhu/xml'),
            'cookiejar' => storage_path('szamlazzhu/.cookiejar'),
            'agent_url' => 'https://www.szamlazz.hu/szamla/',
            'root_cert_file' => app_path('/Services/Billing/Szamlazzhu/Phpapi/cacert.pem'),
            'call_method' => 'auto',
            'return_invoice_number_as_result' => true
        ]
    ]
];