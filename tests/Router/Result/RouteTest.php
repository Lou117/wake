<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Router\Result\Route;

class RouteTest extends TestCase
{
    public function testInstantiation()
    {
        $methods = ["GET", "PATCH", "DELETE"];
        $path = "/test";
        $controller = "TestController::test";
        $name = "foo";

        $route = new Route($methods, $path, $controller, $name);
        $this->assertEquals($methods, $route->allowedMethods);
        $this->assertEquals($path, $route->path);
        $this->assertEquals($controller, $route->controller);
        $this->assertEquals($name, $route->name);
    }

    public function testArguments()
    {
        $arguments = ["foo" => "bar"];

        $route = new Route(["GET"], "/test", "TestController::test");
        $route->setArguments($arguments);
        $this->assertEquals($arguments, $route->getArguments());

        $arguments["foo"] = "baz";
        $route->setArguments($arguments);

        $this->assertArrayHasKey("foo", $route->getArguments());
        $this->assertEquals("baz", $route->getArguments()["foo"]);
    }
}
