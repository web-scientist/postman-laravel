<?php

namespace Webscientist\PostmanLaravel\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use ReflectionMethod;
use Illuminate\Support\Str;
use WebScientist\Postman\Collection\Collection;
use WebScientist\Postman\Services\PostmanService as Postman;

class CollectionService
{
    protected Postman $postman;

    protected Collection $collection;

    public function __construct()
    {
        $this->postman = new Postman();
    }

    public function name(string $name): self
    {
        $this->collection = $this->postman->collection($name);
        return $this;
    }

    public function export(string $name): string
    {
        return json_encode($this->collection, JSON_UNESCAPED_SLASHES);
    }

    public function getRoutes()
    {
        $routes = $this->router->getRoutes()->getRoutes();

        $filtered = $this->filter($routes);

        foreach ($filtered as $key => $route) {
            $this->createRequests($route);
        }
    }

    protected function filter(array $routes)
    {
        $filtered = [];

        foreach ($routes as $route) {
            // filter criteria wip
        }

        return $filtered;
    }

    protected function createRequests($route)
    {
        $prefix = $route->action['prefix'];
        $uses = $route->action['uses'];
        $controllerAction = explode('@', $uses);

        $formSubmitMethods = ['POST', 'PUT', 'PATCH'];


        $description = $this->getDescription(...$controllerAction);

        $method = $route->methods[0];
        $name = $this->transformName($route->action['as']);
        $baseUrl = Config::get('app.url', '{{base_url}}');
        $url = rtrim($baseUrl) . '/' . $route->uri;
        $object = $this->collection;

        if ($prefix != '') {
            $levels = explode('/', $prefix);

            $nested = '';
            foreach ($levels as $key => $level) {
                if ($level == 'api') {
                    continue;
                }
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

    public function getBody(string $class, string $method)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);

        $parameters = $reflectionMethod->getParameters();

        $rules = [];
        foreach ($parameters as $parameter) {
            $dependencyClass = (string) $parameter->getType();
            $dependency = new $dependencyClass();

            if ($dependency instanceof Request) {
                $rules = $dependency->rules();
            }
        }
        return $this->getRules($rules);
    }

    private function getRules(array $rules)
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
        }
        return $fields;
    }


    private function transformName(string $name): string
    {
        $name = Str::replace('.', ' ', $name);
        return Str::title($name);
    }

    private function getDescription(string $class, string $method)
    {
        $reflectionMethod = new ReflectionMethod($class, $method);
        $docComment = $reflectionMethod->getDocComment();

        $docComment = str_replace('*', '', $docComment);
        $docComment = str_replace('/', '', $docComment);
        $docComment = trim($docComment);
        return $docComment;
    }
}
