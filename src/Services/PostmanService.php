<?php

namespace WebScientist\PostmanLaravel\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class PostmanService
{
    protected array $postman = [];

    protected string $name = '';

    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function collection()
    {
        $this->postman['collection'] = (new CollectionService())->name($this->name)->toRaw();
        return $this;
    }

    public function environment()
    {
        $this->postman['environment'] = (new EnvironmentService())->name($this->name)->toRaw();
        return $this;
    }

    public function toRaw(): array
    {
        return $this->postman;
    }

    public function json(): string
    {
        return json_encode($this->postman, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function export(bool $suffixDateTime = false): bool
    {
        $json = $this->json();

        $suffix = $suffixDateTime ? date_format(date_create(), '_YmdHis') : '';

        $filename = Str::snake($this->name) . $suffix . '.postman.json';
        $path = "/postman/{$filename}";

        return Storage::disk('local')->put($path, $json);
    }
}
