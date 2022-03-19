<?php

namespace WebScientist\PostmanLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use WebScientist\PostmanLaravel\Services\CollectionService as Collection;
use WebScientist\PostmanLaravel\Services\EnvironmentService as Environment;

class PostmanExportCommand extends Command

{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:export {name?}
                            {--e|environment : Export environment json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a Postman collection and exports it to the file system';

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
        $name = ($this->option('name') ?? Config::get('app.name')) . '_' . time();
        App::make(Collection::class)
            ->name($name)
            ->export();

        if ($this->option('environment')) {
            App::make(Environment::class)
                ->name($name)
                ->export();
        }

        $this->info('File/s exported successfully');

        return 0;
    }
}
