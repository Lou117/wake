<?php
namespace Lou117\Wake\Middleware;

use Throwable;
use Monolog\Logger;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandlingMiddleware implements MiddlewareInterface
{
    /**
     * @var Logger
     */
    protected Logger $logger;


    public function __construct(Logger $wake_logger)
    {
        $this->logger = $wake_logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $error) {
            $this->logger->error(
                "{$error->getMessage()} ({$error->getFile()} @ line {$error->getLine()})",
                $error->getTrace()
            );

            return new Response(500);
        }
    }
}
