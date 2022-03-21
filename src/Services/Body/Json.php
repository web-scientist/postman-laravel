<?php

namespace WebScientist\PostmanLaravel\Services\Body;

use ReflectionMethod;
use Illuminate\Http\Request;
use WebScientist\PostmanLaravel\Contracts\Body;

class Json implements Body
{
    protected ReflectionMethod $reflectionMethod;

    public function __construct(string $class, string $method)
    {
        $this->reflectionMethod = new ReflectionMethod($class, $method);
    }

    public function getBody(): string|array
    {
        $parameters = $this->reflectionMethod->getParameters();

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

    public function getDescription(): string
    {
        return '';
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

            $fields[$name] = $value;
        };

        return $fields;
    }
}
