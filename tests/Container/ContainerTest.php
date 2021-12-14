<?php

use PHPUnit\Framework\TestCase;
use Lou117\Wake\Container\Container;
use Lou117\Wake\Container\NotFoundException;

class ContainerTest extends TestCase
{
    /**
     * @covers \Lou117\Wake\Container\Container::set
     * @covers \Lou117\Wake\Container\Container::get
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testGet()
    {
        $instance = new Container();
        $instance->set("test", "test");
        $this->assertEquals("test", $instance->get("test"));
    }

    /**
     * @covers \Lou117\Wake\Container\Container::set
     * @covers \Lou117\Wake\Container\Container::has
     * @return void
     */
    public function testHas()
    {
        $instance = new Container();
        $instance->set("test", "test");
        $this->assertTrue($instance->has("test"));
    }

    /**
     * @covers \Lou117\Wake\Container\Container::has
     * @return void
     */
    public function testHasWithNotExistingID()
    {
        $instance = new Container();
        $this->assertFalse($instance->has("test"));
    }

    /**
     * @covers \Lou117\Wake\Container\Container::get
     * @return void
     */
    public function testGetWithNotExistingID()
    {
        $instance = new Container();
        $this->expectException(NotFoundException::class);
        $instance->get("test");
    }
}
