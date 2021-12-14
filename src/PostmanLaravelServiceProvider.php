<?php

namespace WebScientist\PostmanLaravel;

use Illuminate\Support\ServiceProvider;
use WebScientist\PostmanLaravel\Console\Commands\PostmanExportCommand;

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
                PostmanExportCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/postman.php' => config_path('postman.php'),
        ], 'postman');
    }
}
