<?php

namespace WebScientist\PostmanLaravel\Console\Commands;

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
    protected $signature = 'postman:export {--name=} {--t|timestamp}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        App::make(Collection::class)
            ->name($name)
            ->export();

        $this->info('File exported successfully');

        return Command::SUCCESS;
    }
}
