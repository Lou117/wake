<?php
use Lou117\Wake\Router\Router;
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Router\Result\Route;

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
            "controllerFQCNArray" => "toto"
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
            "prefix" => "/toto"
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $routes = $instance->buildRoutingTable();
        $this->testRoutingTable($routes);
    }

    /**
     * @param Route[] $routes
     * @return void
     */
    protected function testRoutingTable(array $routes)
    {
        $this->assertCount(1, $routes);
        $this->assertArrayHasKey(0, $routes);

        $route = $routes[0];
        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals("/toto/test", $route->path);

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
            "prefix" => "/toto",
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
        $this->testRoutingTable($routes);

        // Re-building routing table, presumably from cache
        $this->testRoutingTable($instance->buildRoutingTable());
    }
}
