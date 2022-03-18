<?php


return [
    'api' => [
        'endpoint' => env('POSTMAN_API_ENDPOINT', 'https://api.getpostman.com'),
        'key' => env('POSTMAN_API_KEY', ''),
    ],

    'request' => [
        'excluded_prefixes' => [
            '_ignition',
            'sanctum',
        ],
    ],

    'environment' => [
        'variables' => [
            [
                'key' => 'BASE_URL',
                'value' => env('APP_URL', ''),
                'type' => 'default',
                'enabled' => true,
            ],
            // Other Variables
        ]
    ]
];
