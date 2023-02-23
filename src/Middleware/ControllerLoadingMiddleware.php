<?php declare(strict_types=1);
namespace Lou117\Wake\Middleware;

use ReflectionClass;
use Lou117\Wake\Router\Result\Route;
use Lou117\Wake\Dependency\Resolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ControllerLoadingMiddleware implements MiddlewareInterface
{
    /**
     * @var Resolver
     */
    protected Resolver $dependencyResolver;

    /**
     * @var Route
     */
    protected Route $route;


    /**
     * @param Route $wake_route
     * @param Resolver $wake_dependency_resolver
     */
    public function __construct(Route $wake_route, Resolver $wake_dependency_resolver)
    {
        $this->route = $wake_route;
        $this->dependencyResolver = $wake_dependency_resolver;
    }

    /**
     * @throws \Lou117\Wake\Container\NotFoundException
     * @throws \ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matches = [];
        preg_match_all("#(?<class>.+)::(?<method>.+)#", $this->route->controller, $matches);

        $class = $matches["class"][0];
        $method = $matches["method"][0];

        $reflectionClass = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getConstructor();

        if ($reflectionMethod !== null) {
            $controller = $reflectionClass->newInstance(...$this->dependencyResolver->resolve(
                $reflectionMethod->getParameters()
            ));
        } else {
            $controller = new $class();
        }

        $reflectionMethod = $reflectionClass->getMethod($method);
        return $controller->{$method}(...$this->dependencyResolver->resolve(
            $reflectionMethod->getParameters()
        ));
    }
}
