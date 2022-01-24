<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Router\Result\Route;
use Lou117\Wake\Dependency\Resolver;
use Lou117\Wake\Container\Container;
use Psr\Http\Message\ResponseInterface;
use Lou117\Wake\Middleware\ControllerLoadingMiddleware;

require_once(__DIR__."/../TestController.php");
require_once(__DIR__."/../TestRequestHandler.php");

class ControllerLoadingMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $container = new Container();
        $container->set("constructor_dependency", "test1");
        $container->set("method_dependency", "test2");

        $instance = new ControllerLoadingMiddleware(
            new Route(["GET"], "/test", "TestController::test"),
            new Resolver($container)
        );

        $request = new ServerRequest("GET", "/test");
        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $instance->process($request, new TestRequestHandler());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("test1", $response->getHeaderLine("X-Constructor-Dependency"));
        $this->assertEquals("test2", $response->getHeaderLine("X-Method-Dependency"));
    }
}
