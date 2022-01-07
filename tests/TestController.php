<?php

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class TestController
{
    protected string $constructorDependency;

    protected string $methodDependency;


    /**
     * @param string $dependency
     */
    public function __construct(string $constructor_dependency)
    {
        $this->constructorDependency = $constructor_dependency;
    }

    /**
     * @param string $dependency
     * @return ResponseInterface
     */
    public function test(string $method_dependency): ResponseInterface
    {
        $this->methodDependency = $method_dependency;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new Response(418))
            ->withAddedHeader("X-Constructor-Dependency", $this->constructorDependency)
            ->withAddedHeader("X-Method-Dependency", $this->methodDependency);
    }
}
