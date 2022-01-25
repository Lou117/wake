<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Router\RoutingTable;
use Lou117\Wake\Router\Result\Route;

class RoutingTableTest extends TestCase
{
    public function testInstantiation()
    {
        $instance = new RoutingTable([
            new Route(["GET"], "/test", "TestController::test")
        ]);

        $this->assertFalse($instance->fromCache);

        $this->assertInstanceOf(Countable::class, $instance);
        $this->assertCount(1, $instance);

        $this->assertInstanceOf(ArrayAccess::class, $instance);
        $this->assertArrayHasKey(0, $instance);
        $this->assertInstanceOf(Route::class, $instance[0]);

        $this->assertInstanceOf(Iterator::class, $instance);

        foreach ($instance as $index => $route) {
            $this->assertSame($route, $instance[$index]);
        }
    }

    /**
     * @depends testInstantiation
     * @return void
     */
    public function testInstantiationWithInvalidRoutes()
    {
        $instance = new RoutingTable([
            new Route(["GET"], "/test", "TestController::test"),
            new stdClass()
        ]);
        $this->assertCount(1, $instance);
    }

    public function testInstantiationWithCache()
    {
        $instance = new RoutingTable([
            new Route(["GET"], "/test", "TestController::test")
        ], true);
        $this->assertTrue($instance->fromCache);
    }

    /**
     * @return void
     */
    public function testCanOnlyAddRoute()
    {
        $instance = new RoutingTable([]);
        $this->expectException(InvalidArgumentException::class);
        $instance[] = new stdClass();
    }
}
