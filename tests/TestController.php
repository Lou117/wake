<?php
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Lou117\Wake\Router\PathAttribute as WakeRouterPath;
use Lou117\Wake\Router\MethodAttribute as WakeRouterMethod;

class TestController
{
    protected string $constructorDependency;

    protected string $methodDependency;


    /**
     * @param string $constructor_dependency
     */
    public function __construct(string $constructor_dependency)
    {
        $this->constructorDependency = $constructor_dependency;
    }

    /**
     * @param string $method_dependency
     * @return ResponseInterface
     */
    #[WakeRouterMethod(WakeRouterMethod::METHOD_GET)]
    #[WakeRouterPath("/test")]
    public function test(string $method_dependency): ResponseInterface
    {
        $this->methodDependency = $method_dependency;

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new Response(418))
            ->withAddedHeader("X-Constructor-Dependency", $this->constructorDependency)
            ->withAddedHeader("X-Method-Dependency", $this->methodDependency);
    }

    /**
     * @return ResponseInterface
     */
    #[WakeRouterMethod(WakeRouterMethod::METHOD_PUT)]
    #[WakeRouterPath("/test/{id}")]
    public function testWithArgument(): ResponseInterface
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return new Response(418);
    }
}
