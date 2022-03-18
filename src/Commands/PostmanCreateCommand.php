<?php

namespace WebScientist\PostmanLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use WebScientist\PostmanLaravel\Concerns\Api;
use WebScientist\PostmanLaravel\Services\CollectionService as Collection;

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
        $name = $this->option('name') ?? Config::get('app.name');
        $json = App::make(Collection::class)
            ->name($name)
            ->toJson(true);

        $response = $this->http()
            ->withBody($json, 'application/json')
            ->post('collections');

        if ($response->failed()) {
            $this->warn($response->json('error.message'));
            $this->warn('Check you POSTMAN_API_KEY in .env file');
            return 0;
        }

        $this->info('Collection created on Postman.');

        return 0;
    }
}
