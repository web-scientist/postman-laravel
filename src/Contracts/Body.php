<?php

namespace WebScientist\PostmanLaravel\Contracts;

interface Body
{
    public function __construct(string $class, string $method);

    public function getBody(): string|array;

    public function getDescription(): string;
}
