<?php

namespace WebScientist\PostmanLaravel\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use WebScientist\PostmanLaravel\Concerns\Api;
use WebScientist\PostmanLaravel\Services\CollectionService as Collection;
use WebScientist\PostmanLaravel\Services\EnvironmentService as Environment;

class PostmanCreateCommand extends Command

{
    use Api;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:create {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Postman collection on your Postman workspace.';

    protected $name = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->name = $this->option('name') ?? Config::get('app.name');

        $collection = App::make(Collection::class)
            ->name($this->name)
            ->toJson(true);

        $proceed = $this->callPostman('collections', $collection);

        if (!$proceed) {
            return 0;
        }

        $environment = App::make(Environment::class)
            ->name($this->name)
            ->toJson(true);
        $this->callPostman('environments', $environment);


        $this->info("Collection and Environment created on Postman.");

        return 0;
    }

    public function callPostman(string $url, string $json)
    {
        $response = $this->http()
            ->withBody($json, 'application/json')
            ->post($url);

        if ($response->failed()) {
            $this->warn($response->json('error.message'));
            $this->warn('Check you POSTMAN_API_KEY in .env file');
            return 0;
        }

        return 1;
    }
}
