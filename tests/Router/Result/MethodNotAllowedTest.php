<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Router\Result\AbstractResult;
use Lou117\Wake\Router\Result\MethodNotAllowed;

class MethodNotAllowedTest extends TestCase
{
    public function testIsAbstractResult()
    {
        $instance = new MethodNotAllowed([]);
        $this->assertInstanceOf(AbstractResult::class, $instance);
    }

    public function testHasAllowedMethods()
    {
        $instance = new MethodNotAllowed(["GET", "PATCH", "DELETE"]);
        $this->assertEquals(["GET", "PATCH", "DELETE"], $instance->allowedMethods);
    }
}
