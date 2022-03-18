<?php

namespace WebScientist\PostmanLaravel\Services;

use Closure;
use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use WebScientist\Postman\Collection\Collection;
use WebScientist\Postman\Services\PostmanService as Postman;

class CollectionService
{
    protected Postman $postman;

    protected Collection $collection;

    protected Router $router;

    protected array $depth = [];

    public function __construct()
    {
        $this->postman = App::make(Postman::class);
        $this->router = App::make(Router::class);
    }

    public function name(string $name): self
    {
        $this->collection = $this->postman->collection($name);
        return $this;
    }

    public function toRaw(): Collection
    {
        $this->getRoutes();
        return $this->collection;
    }

    public function json(): string
    {
        $this->getRoutes();
        return json_encode($this->collection, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function toJson(bool $keyWrapper = false, int $flags = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT): string
    {
        $this->getRoutes();
        return $keyWrapper
            ? json_encode(['collection' => $this->collection], $flags)
            : json_encode($this->collection, $flags);
    }

    public function export(bool $suffixDateTime = false): bool
    {
        $json = $this->json();
        $name = $this->collection->info['name'];

        $suffix = $suffixDateTime ? date_format(date_create(), '_YmdHis') : '';

        $filename = Str::snake($name) . $suffix . '.postman_collection.json';
        $path = "/postman/{$filename}";

        return Storage::disk('local')->put($path, $json);
    }

    protected function getRoutes()
    {
        $routes = $this->router->getRoutes()->get();

        $filtered = $this->filter($routes);

        foreach ($filtered as $route) {
            $this->createRequests($route);
        }
    }

    protected function filter(array $routes)
    {
        $filtered = [];

        foreach ($routes as $route) {
            if (!in_array('api', $route->action['middleware'])) {
                continue;
            }
            $uris = explode('/', $route->uri);

            if (count($uris) === 1) {
                continue;
            }

            $excludedUriPrefixes = Config::get('postman.request.excluded_prefixes');

            if (in_array($uris[0], $excludedUriPrefixes)) {
                continue;
            }

            if ($route->action['uses'] instanceof Closure) {
                continue;
            }
            $filtered[] = $route;
        }
        return $filtered;
    }

    protected function createRequests($route)
    {
        $uses = $route->action['uses'];

        $controllerAction = explode('@', $uses);

        $formSubmitMethods = ['POST', 'PUT', 'PATCH'];


        $description = $this->getDescription(...$controllerAction);

        $method = $route->methods[0];
        $as = $route->action['as'] ?? '';
        $name = $this->nameOrPath($route);
        $baseUrl = Config::get('app.url', '{{base_url}}');
        $url = rtrim($baseUrl) . '/' . $route->uri;
        $object = $this->collection;

        if ($as != '') {
            $levels = explode('.', $as);
            array_pop($levels);

            foreach ($levels as $level) {
                $level = $this->transformName($level);
                if ($object->{$level} === null) {
                    $object = $object->item($level);
                    continue;
                }
                $object = $object->{$level};
            }
        }

        $object = $object->request($name, $method)->url($url)->description($description);

        $body = [];
        if (in_array($route->methods[0], $formSubmitMethods)) {
            $body = $this->getBody(...$controllerAction);

            $body = $object->body('formdata', $body);
        }
    }

    protected function getBody(string $class, string $method)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);

        $parameters = $reflectionMethod->getParameters();

        $rules = [];
        foreach ($parameters as $parameter) {
            $dependencyClass = (string) $parameter->getType();

            if (empty($dependencyClass)) {
                continue;
            }

            $dependency = new $dependencyClass();

            if (!($dependency instanceof Request)) {
                continue;
            }

            if (method_exists($dependency, 'rules')) {
                $rules = $dependency->rules();
            }
        }
        return $this->getRules($rules);
    }

    protected function getRules(array $rules)
    {
        $fields = [];

        foreach ($rules as $name => $rule) {

            if (is_array($rule)) {
                $rule = implode(" | ", $rule);
            }

            $value = '';

            $fields[] = [
                'key' => $name,
                'value' => $value,
                'description' => $rule,
            ];
        };
        return $fields;
    }


    protected function transformName(string $name): string
    {
        $name = Str::replace('.', ' ', $name);
        return Str::title($name);
    }

    protected function getDescription(string $class, string $method)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);
        $docComment = $reflectionMethod->getDocComment();
        $lines = explode("\n", $docComment);
        array_pop($lines);
        array_shift($lines);
        foreach ($lines as &$line) {
            $line = ltrim($line);
            $line = ltrim($line, "*");
            $line = ltrim($line);
        }

        return $lines[0];
    }

    protected function nameOrPath($route)
    {
        $action = $route->action;
        if (array_key_exists('as', $action)) {
            return $this->transformName($action['as']);
        }

        $string = Str::replace('/', ' ', $route->uri);
        return Str::ucfirst($string);
    }
}
