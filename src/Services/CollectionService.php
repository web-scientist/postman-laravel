<?php

namespace WebScientist\PostmanLaravel\Services;

use Closure;
use ReflectionMethod;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
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

    /**
     * Instantiate a collection with the name supplied
     * 
     * @param string $name The name of the collection
     * @return CollectionService
     */
    public function name(string $name): self
    {
        $this->collection = $this->postman->collection($name);
        return $this;
    }

    /**
     * Return a raw Postman Collection object
     * 
     * @return Collection
     */
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

    /**
     * Sets the routes that are required for Postman collection into the collection property
     * 
     * @return void
     */
    protected function getRoutes(): void
    {
        $routes = $this->router->getRoutes()->get();

        $filtered = $this->filter($routes);

        foreach ($filtered as $route) {
            $this->createRequests($route);
        }
    }

    /**
     * Returns an array of filtered routes as defined in the configuration
     * 
     * @param array $routes An array of all routes
     * @return array
     */
    protected function filter(array $routes): array
    {
        $filtered = [];

        foreach ($routes as $route) {

            if ($route->action['uses'] instanceof Closure) {
                continue;
            }

            $configMiddlewares = Config::get('postman.request.inclusion.middleware');
            $routeMiddlewares = $route->action['middleware'];

            $intersection = array_intersect($configMiddlewares, $routeMiddlewares);

            if (count($intersection)) {
                $filtered[] = $route;
                continue;
            }
            $uris = explode('/', $route->uri);

            if (count($uris) === 1) {
                continue;
            }

            $excludedUriPrefixes = Config::get('postman.request.exclusion.prefix');

            if (in_array($uris[0], $excludedUriPrefixes)) {
                continue;
            }

            $filtered[] = $route;
        }
        return $filtered;
    }

    /**
     * Creates a Postman Request from the given route
     * 
     * @param \Illuminate\Routing\Route $route
     * @return void
     */
    protected function createRequests(Route $route): void
    {
        $uses = $route->action['uses'];

        $controllerAction = explode('@', $uses);

        $formSubmitMethods = ['POST', 'PUT', 'PATCH'];


        $description = $this->getDescription(...$controllerAction);

        $method = $route->methods[0];
        $name = $this->nameOrPath($route);
        $url = $route->uri;
        $object = $this->collection;


        $levels = $this->getGroupLevels($route->action);

        foreach ($levels as $level) {
            $level = $this->transformName($level);
            if ($object->{$level} === null) {
                $object = $object->item($level);
                continue;
            }
            $object = $object->{$level};
        }


        $object = $object->request($name, $method)->url($url)->description($description);

        $body = [];
        if (in_array($route->methods[0], $formSubmitMethods)) {
            $body = $this->getBody(...$controllerAction);

            $body = $object->body('formdata', $body);
        }
    }

    /**
     * Get the grouping array for nested requests in Postman
     * 
     * @param array $action The route action
     * @return array
     */
    protected function getGroupLevels(array $action): array
    {
        $groupBy = Config::get('postman.request.group_by');

        if ($groupBy == 'name') {
            $as = $action['as'] ?? '';
            $levels = explode('.', $as);
            array_pop($levels);
            return $levels;
        }

        if ($groupBy == 'tag') {
            $tag = $action['tag'] ?? '';
            $levels = explode('.', $tag);
            return $levels;
        }
    }

    /**
     * Get the body for the Request object
     * 
     * @param string $class The controller class name
     * @param string $method The controller method name
     */
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
        return $this->getFields($rules);
    }

    /**
     * Gets the form body fields based upon Request class rules
     * 
     * @param array $rules The form request class rules
     * @return array
     */
    protected function getFields(array $rules): array
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

    /**
     * Transform the name for Request or Folder name
     * 
     * @param string $name
     * @return string
     */
    protected function transformName(string $name): string
    {
        $name = Str::replace('.', ' ', $name);
        return Str::title($name);
    }

    /**
     * Get the description for a request from the class method's Docblock
     * 
     * @param string $class The controller class name
     * @param string $method The controller method name
     * @return string
     */
    protected function getDescription(string $class, string $method): string
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

    /**
     * Use the path as name if name is not present in route
     * 
     * @param \Illuminate\Routing\Route $route
     * @return string
     */
    protected function nameOrPath(Route $route): string
    {
        $action = $route->action;
        if (array_key_exists('as', $action)) {
            return $this->transformName($action['as']);
        }

        $string = Str::replace('/', ' ', $route->uri);
        return Str::ucfirst($string);
    }
}
