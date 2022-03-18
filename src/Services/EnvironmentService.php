<?php

namespace WebScientist\PostmanLaravel\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use WebScientist\Postman\Environment\Environment;
use WebScientist\Postman\Services\PostmanService as Postman;

class EnvironmentService
{
    protected Postman $postman;

    protected Environment $environment;

    public function __construct()
    {
        $this->postman = App::make(Postman::class);
    }

    public function name(string $name): self
    {
        $exportedUsing = 'Laravel/' . App::version();
        $this->environment = $this->postman->environment($name, $exportedUsing);
        return $this;
    }

    public function json(): string
    {
        $this->setValues('BASE_URL', '');
        return json_encode($this->environment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function setValues(string $key, string $value)
    {
        $this->environment->values($key, $value);
    }

    public function export(bool $suffixDateTime = false): bool
    {
        $json = $this->json();
        $name = $this->environment->name;

        $suffix = $suffixDateTime ? date_format(date_create(), '_YmdHis') : '';

        $filename = Str::snake($name) . $suffix . '.postman_environment.json';
        $path = "/postman/{$filename}";

        return Storage::disk('local')->put($path, $json);
    }
}
