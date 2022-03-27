<?php

namespace WebScientist\PostmanLaravel;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use WebScientist\PostmanLaravel\Commands;
use WebScientist\PostmanLaravel\Contracts\Body;
use WebScientist\PostmanLaravel\Services\Body\Json;
use WebScientist\PostmanLaravel\Services\Body\FormData;

class PostmanLaravelServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/postman.php',
            'postman'
        );

        $bodyMode = Config::get('postman.request.body_mode');

        if ($bodyMode == 'formdata') {
            $this->app->bind(Body::class, FormData::class);
        }
        if ($bodyMode == 'json') {
            $this->app->bind(Body::class, Json::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\PostmanExportCommand::class,
                Commands\PostmanCreateCommand::class
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/postman.php' => App::configPath('postman.php'),
        ], 'postman');
    }
}
