<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Router\Result\AbstractResult;
use Lou117\Wake\Router\Result\MethodNotAllowed;

class NotFoundTest extends TestCase
{
    public function testIsAbstractResult()
    {
        $instance = new MethodNotAllowed([]);
        $this->assertInstanceOf(AbstractResult::class, $instance);
    }
}
