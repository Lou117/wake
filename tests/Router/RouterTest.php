<?php
use Lou117\Wake\Router\Router;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Router\Result\Route;
use Lou117\Wake\Router\RoutingTable;
use Lou117\Wake\Router\Result\NotFound;
use Lou117\Wake\Router\Result\MethodNotAllowed;

require_once(__DIR__."/../TestController.php");

class RouterTest extends TestCase
{
    public function testInstantiationWithDefaultConfiguration()
    {
        $this->assertInstanceOf(Router::class, new Router(Router::getDefaultConfiguration()));
    }

    public function testInstantiation()
    {
        $injectedConfiguration = [
            "controllerFQCNArray" => [
                "TestController"
            ],
            "prefix" => "/test",
            "cache" => [
                "enabled" => true,
                "fastRouteCacheFilepath" => "/tmp/fastRouteCacheFile",
                "tableCacheFilepath" => "/tmp/tableCacheFile"
            ]
        ];

        $router = new Router($injectedConfiguration);

        $routerConfiguration = $router->configuration;
        $this->assertIsArray($routerConfiguration["controllerFQCNArray"]);
        $this->assertCount(
            count($injectedConfiguration["controllerFQCNArray"]),
            $routerConfiguration["controllerFQCNArray"]
        );
        $this->assertArrayHasKey(0, $routerConfiguration["controllerFQCNArray"]);
        $this->assertEquals(
            $injectedConfiguration["controllerFQCNArray"][0],
            $routerConfiguration["controllerFQCNArray"][0]
        );

        $this->assertEquals($injectedConfiguration["prefix"], $routerConfiguration["prefix"]);

        $this->assertIsArray($routerConfiguration["cache"]);
        $this->assertArrayHasKey("enabled", $routerConfiguration["cache"]);
        $this->assertTrue($routerConfiguration["cache"]["enabled"]);
        $this->assertArrayHasKey("fastRouteCacheFilepath", $routerConfiguration["cache"]);
        $this->assertEquals(
            $injectedConfiguration["cache"]["fastRouteCacheFilepath"],
            $routerConfiguration["cache"]["fastRouteCacheFilepath"]
        );
        $this->assertArrayHasKey("tableCacheFilepath", $routerConfiguration["cache"]);
        $this->assertEquals(
            $injectedConfiguration["cache"]["tableCacheFilepath"],
            $routerConfiguration["cache"]["tableCacheFilepath"]
        );
    }

    public function testInstantiationWithInvalidControllerFQCNList()
    {
        $this->expectException(RuntimeException::class);
        new Router([
            "controllerFQCNArray" => "foo"
        ]);
    }

    public function testInstantiationWithInvalidControllerFQCN()
    {
        $this->expectException(RuntimeException::class);
        new Router([
            "controllerFQCNArray" => [
                true
            ]
        ]);
    }

    public function testRoutingTableBuildingWithNoCache()
    {
        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ],
            "prefix" => "/foo"
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $routingTable = $instance->buildRoutingTable();
        $this->assertInstanceOf(RoutingTable::class, $routingTable);
        $this->assertFalse($routingTable->fromCache);
        $this->testRoutingTable($routingTable);
    }

    /**
     * @param RoutingTable $routes
     * @return void
     */
    protected function testRoutingTable(RoutingTable $routes)
    {
        $this->assertCount(2, $routes);
        $this->assertArrayHasKey(0, $routes);

        $route = $routes[0];
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals("/foo/test", $route->path);

        $this->assertCount(1, $route->allowedMethods);
        $this->assertArrayHasKey(0, $route->allowedMethods);
        $this->assertEquals("GET", $route->allowedMethods[0]);

        $this->assertEquals("TestController::test", $route->controller);
    }

    public function testRoutingTableBuildingWithCache()
    {
        $fastRouteCacheFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
        $tableCacheFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();

        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ],
            "prefix" => "/foo",
            "cache" => [
                "enabled" => true,
                "fastRouteCacheFilepath" => $fastRouteCacheFilepath,
                "tableCacheFilepath" => $tableCacheFilepath
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $instance->buildRoutingTable();
        $this->assertFileExists($tableCacheFilepath);
        $this->assertFileIsReadable($tableCacheFilepath);

        $routes = unserialize(file_get_contents($tableCacheFilepath));
        $routingTable = new RoutingTable($routes);
        $this->testRoutingTable($routingTable);

        /** @noinspection PhpUnhandledExceptionInspection */
        $routingTable = $instance->buildRoutingTable();
        $this->assertInstanceOf(RoutingTable::class, $routingTable);
        $this->assertTrue($routingTable->fromCache);
        $this->testRoutingTable($routingTable);
    }

    public function testDispatchWithNoCache()
    {
        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $routingTable = $instance->buildRoutingTable();

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $instance->dispatch(new ServerRequest("GET", "/test"), $routingTable);
        $this->assertInstanceOf(Route::class, $result);

        $this->assertIsArray($result->allowedMethods);
        $this->assertArrayHasKey(0, $result->allowedMethods);
        $this->assertEquals("GET", $result->allowedMethods[0]);

        $this->assertEquals("/test", $result->path);
        $this->assertEquals("TestController::test", $result->controller);
        $this->assertEquals("bar", $result->name);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $instance->dispatch(
            new ServerRequest("PUT", "/test/117"),
            $routingTable
        );
        $this->assertInstanceOf(Route::class, $result);

        $this->assertArrayHasKey(0, $result->allowedMethods);
        $this->assertEquals("PUT", $result->allowedMethods[0]);

        $this->assertEquals("/test/{id}", $result->path);
        $this->assertEquals("TestController::testWithArgument", $result->controller);
        $this->assertNull($result->name);

        $this->assertIsArray($result->getArguments());
        $this->assertArrayHasKey("id", $result->getArguments());
        $this->assertEquals("117", $result->getArguments()["id"]);
    }

    public function testDispatchWithCache()
    {
        $fastRouteCacheFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();
        $tableCacheFilepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid();

        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ],
            "cache" => [
                "enabled" => true,
                "fastRouteCacheFilepath" => $fastRouteCacheFilepath,
                "tableCacheFilepath" => $tableCacheFilepath
            ]
        ]);

        $routingTable = $instance->buildRoutingTable();

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $instance->dispatch(new ServerRequest("GET", "/test"), $routingTable);
        $this->assertInstanceOf(Route::class, $result);
        $this->assertFileExists($fastRouteCacheFilepath);

        /**
         * Dispatching request again, with FastRoute presumably fetching from its own cache.
         * @noinspection PhpUnhandledExceptionInspection
         */
        $result = $instance->dispatch(new ServerRequest("GET", "/test"), $routingTable);
        $this->assertInstanceOf(Route::class, $result);
    }

    public function testDispatchWithNotFoundExpected()
    {
        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $instance->dispatch(
            new ServerRequest("GET", "/foo"),
            $instance->buildRoutingTable()
        );
        $this->assertInstanceOf(NotFound::class, $result);
    }

    public function testDispatchWithMethodNotAllowedExpected()
    {
        $instance = new Router([
            "controllerFQCNArray" => [
                "TestController"
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $instance->dispatch(
            new ServerRequest("PATCH", "/test"),
            $instance->buildRoutingTable()
        );
        $this->assertInstanceOf(MethodNotAllowed::class, $result);
        $this->assertArrayHasKey(0, $result->allowedMethods);
        $this->assertEquals("GET", $result->allowedMethods[0]);
    }
}
