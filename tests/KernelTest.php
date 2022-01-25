<?php

use Monolog\Logger;
use Lou117\Wake\Kernel;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Configuration\Configuration;

require_once(__DIR__.DIRECTORY_SEPARATOR."TestMiddleware1.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."TestMiddleware2.php");

class KernelTest extends TestCase
{
    public function testInstantiation()
    {
        $instance = new Kernel();
        $this->assertInstanceOf(Kernel::class, $instance);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertInstanceOf(Configuration::class, $instance->getConfiguration());
    }

    public function testHandle()
    {
        $instance = (new Kernel())->loadConfiguration([
            Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [
                TestMiddleware1::class,
                TestMiddleware2::class
            ]
        ]);

        $response = $instance->handle(new ServerRequest("GET", "/test"));

        $this->assertEquals("foo", $response->getHeaderLine("X-Middleware-1"));

        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("bar", $response->getHeaderLine("X-Middleware-2"));
    }

    public function testHandleWithInvalidMiddleware()
    {
        $instance = (new Kernel())->loadConfiguration([
            Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [
                TestMiddleware1::class,
                stdClass::class
            ]
        ]);

        $this->expectException(LogicException::class);
        $instance->handle(new ServerRequest("GET", "/test"));
    }

    /**
     * @depends testInstantiation
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testLoadConfiguration()
    {
        $instance = new Kernel();
        /** @noinspection PhpUnhandledExceptionInspection */
        $instance->loadConfiguration([
            "foo" => "bar"
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertInstanceOf(Configuration::class, $instance->getConfiguration());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals("bar", $instance->getConfiguration()->get("foo"));

        /** @noinspection PhpUnhandledExceptionInspection */
        $instance->loadConfiguration([
            "foo" => "baz"
        ]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals("baz", $instance->getConfiguration()->get("foo"));
    }

    public function testRunWithRequest()
    {
        $instance = new Kernel();
        /** @noinspection PhpUnhandledExceptionInspection */
        $instance->loadConfiguration([
            Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [
                TestMiddleware1::class,
                TestMiddleware2::class
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $instance->run(new Request("GET", "/test"));
        $this->assertEquals("foo", $response->getHeaderLine("X-Middleware-1"));

        $this->assertEquals(418, $response->getStatusCode());
        $this->assertEquals("bar", $response->getHeaderLine("X-Middleware-2"));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertInstanceOf(Logger::class, $instance->getLogger());
    }

    public function testRunWithEmptyMiddlewareSequence()
    {
        $instance = new Kernel();
        $this->expectException(RuntimeException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $instance->run(new Request("GET", "/test"));
    }
}
