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
];
