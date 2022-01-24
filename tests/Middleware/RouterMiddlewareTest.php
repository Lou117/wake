<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Container\Container;
use Lou117\Wake\Dependency\Provider;
use Lou117\Wake\Router\Result\Route;
use Lou117\Wake\Middleware\RouterMiddleware;
use Lou117\Wake\Configuration\Configuration;

require_once(__DIR__."/../TestController.php");

class RouterMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $container = new Container();
        $provider = new Provider($container);

        $instance = new RouterMiddleware((new Configuration())->import([
            "wake-router" => [
                "controllerFQCNArray" => [
                    "TestController"
                ]
            ]
        ]), $provider);

        $response = $instance->process(
            new ServerRequest("GET", "/test"),
            new TestRequestHandler()
        );

        $this->assertEquals(418, $response->getStatusCode());

        $this->assertTrue($container->has("wake_route"));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertInstanceOf(Route::class, $container->get("wake_route"));

        /**
         * @var Route $route
         * @noinspection PhpUnhandledExceptionInspection
         */
        $route = $container->get("wake_route");
        $this->assertEquals(["GET"], $route->allowedMethods);
        $this->assertEquals("/test", $route->path);
        $this->assertEquals("TestController::test", $route->controller);
    }
}
