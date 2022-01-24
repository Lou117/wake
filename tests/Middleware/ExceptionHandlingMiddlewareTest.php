<?php
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Middleware\ExceptionHandlingMiddleware;

require_once(__DIR__."/../TestExceptionRequestHandler.php");

class ExceptionHandlingMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $handler = new TestHandler();
        $logger = new Logger("test", [
            $handler
        ]);

        $middleware = new ExceptionHandlingMiddleware($logger);
        $response = $middleware->process(
            new ServerRequest("GET", "/test"),
            new TestExceptionRequestHandler()
        );

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertTrue($handler->hasErrorThatMatches("#testexception#"));
    }
}
