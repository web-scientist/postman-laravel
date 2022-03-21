<?php


return [
    'api' => [
        'endpoint' => env('POSTMAN_API_ENDPOINT', 'https://api.getpostman.com'),
        'key' => env('POSTMAN_API_KEY', ''),
    ],

    'auth_type' => 'bearer',

    'request' => [
        'group_by' => 'name', // Default set to name, You can overide it with any custom key like 'tag'

        'body' => [
            'default' => 'json',

            'modes' => [
                'formdata' => \WebScientist\PostmanLaravel\Services\Body\FormData::class,
                'json' => \WebScientist\PostmanLaravel\Services\Body\Json::class,
            ],
        ],

        'inclusion' => [
            'middleware' => [
                'api'
            ],
        ],

        'exclusion' => [
            'prefix' => [
                '_ignition',
                'sanctum',
            ],
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
