<?php

namespace WebScientist\PostmanLaravel\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

trait Api
{
    public function http(): PendingRequest
    {
        return Http::withOptions([
            'base_uri' => Config::get('postman.api.endpoint')
        ])
            ->withHeaders([
                'X-Api-Key' => Config::get('postman.api.key')
            ]);
    }
}
