<?php

namespace WebScientist\PostmanLaravel\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
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

    public function toRaw(): Environment
    {
        $this->values();
        return $this->environment;
    }

    public function json(): string
    {
        $this->values();
        return json_encode($this->environment, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function toJson(bool $keyWrapper = false, int $flags = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT): string
    {
        $this->values();
        return $keyWrapper
            ? json_encode(['environment' => $this->environment], $flags)
            : json_encode($this->environment, $flags);
    }

    public function values(): self
    {
        $values = Config::get('postman.environment.variables', []);
        $this->environment->values($values);
        return $this;
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
