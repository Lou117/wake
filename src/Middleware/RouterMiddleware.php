<?php
namespace Lou117\Wake\Middleware;

use GuzzleHttp\Psr7\Response;
use Lou117\Wake\Router\Router;
use Lou117\Wake\ResponseFactory;
use Lou117\Wake\Dependency\Provider;
use Psr\Http\Message\ResponseInterface;
use Lou117\Wake\Router\Result\NotFound;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lou117\Wake\Configuration\Configuration;
use Lou117\Wake\Router\Result\MethodNotAllowed;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var Provider
     */
    protected Provider $dependencyProvider;

    /**
     * @var Router
     */
    protected Router $router;


    public function __construct(Configuration $wake_configuration, Provider $wake_dependency_provider)
    {
        $this->router = new Router($wake_configuration->get(Router::CONFIGURATION_DIRECTIVE) ?? []);
        $this->dependencyProvider = $wake_dependency_provider;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $result = $this->router->dispatch($request);

        if ($result instanceof NotFound) {
            return new Response(404);
        }

        if ($result instanceof MethodNotAllowed) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return (new Response(405))->withAddedHeader(
                ResponseFactory::HTTP_HEADER_ALLOW,
                implode(", ", $result->allowedMethods)
            );
        }

        $this->dependencyProvider->provide("wake_route", $result);
        return $handler->handle($request);
    }
}
