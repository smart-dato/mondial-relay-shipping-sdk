<?php

return [
    'sandbox' => env('MONDIAL_RELAY_SHIPPING_SANDBOX', true),

    'credentials' => [
        'login' => env('MONDIAL_RELAY_SHIPPING_LOGIN'),
        'password' => env('MONDIAL_RELAY_SHIPPING_PASSWORD'),
        'customer_id' => env('MONDIAL_RELAY_SHIPPING_CUSTOMER_ID'),
    ],

    'culture' => env('MONDIAL_RELAY_SHIPPING_CULTURE', 'fr-FR'),

    'output' => [
        'type' => env('MONDIAL_RELAY_SHIPPING_OUTPUT_TYPE', 'PdfUrl'),
        'format' => env('MONDIAL_RELAY_SHIPPING_OUTPUT_FORMAT', '10x15'),
    ],

    'timeout' => (int) env('MONDIAL_RELAY_SHIPPING_TIMEOUT', 30),
];
