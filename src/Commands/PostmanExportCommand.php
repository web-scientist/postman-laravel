<?php

namespace WebScientist\PostmanLaravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use WebScientist\PostmanLaravel\Services\CollectionService as Collection;

class PostmanExportCommand extends Command

{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:export {--name=}';

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

        $this->info('File exported successfully');

        return 0;
    }
}
