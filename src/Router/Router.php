<?php
namespace Lou117\Wake\Router;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use ReflectionAttribute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Lou117\Wake\Router\Result\Route;
use Psr\Http\Message\RequestInterface;
use Lou117\Wake\Router\Result\AbstractResult;
use Lou117\Wake\Router\Result\NotFound as NotFoundResult;
use Lou117\Wake\Router\Result\MethodNotAllowed as MethodNotAllowedResult;

class Router
{
    const CONFIGURATION_DIRECTIVE = "wake-router";


    /**
     * @var array
     */
    public readonly array $configuration;

    /**
     * @var string[]
     */
    public readonly array $controllers;


    public function __construct(array $configuration)
    {
        $sanitizedConfiguration = array_replace_recursive(
            self::getDefaultConfiguration(),
            $configuration
        );

        if (is_array($sanitizedConfiguration["controllerFQCNArray"]) === false) {
            throw new RuntimeException(self::CONFIGURATION_DIRECTIVE.".controllerFQCNArray is not an array");
        }

        if ($sanitizedConfiguration["prefix"] !== null) {
            $prefix = trim($sanitizedConfiguration["prefix"]);
            $sanitizedConfiguration["prefix"] = empty($prefix) ? null : $prefix;
        }

        $sanitizedConfiguration["cache"]["enabled"] = (bool) $sanitizedConfiguration["cache"]["enabled"];

        foreach (["fastRouteCacheFilepath", "tableCacheFilepath"] as $key) {
            if ($sanitizedConfiguration["cache"][$key] !== null) {
                $filepath = trim((string) $sanitizedConfiguration["cache"][$key]);
                $sanitizedConfiguration["cache"][$key] = empty($filepath) ? null : $filepath;
            }
        }

        $this->configuration = $sanitizedConfiguration;
        $this->controllers = array_map(function($candidate) {
            if (is_string($candidate) === false) {
                throw new RuntimeException("One of ". self::CONFIGURATION_DIRECTIVE.".controllerFQCNArray values is not a string");
            }

            return $candidate;
        }, $this->configuration["controllerFQCNArray"]);
    }

    /**
     * Returns Wake router routing table.
     *
     * @return RoutingTable
     * @throws \ReflectionException
     */
    public function buildRoutingTable(): RoutingTable
    {
        if (
            $this->configuration["cache"]["enabled"]
            && file_exists($this->configuration["cache"]["tableCacheFilepath"])
            && is_readable($this->configuration["cache"]["tableCacheFilepath"])
        ) {
            $routes = unserialize(file_get_contents($this->configuration["cache"]["tableCacheFilepath"]));
            return new RoutingTable($routes, true);
        }

        /**
         * @var Route[] $routes
         */
        $routes = [];
        $prefix = $this->configuration["prefix"];

        foreach ($this->controllers as $controllerFQCN) {
            $reflectionClass = new ReflectionClass($controllerFQCN);

            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                /**
                 * @var ReflectionAttribute[] $attributes
                 */
                $attributes = array_reduce(
                    $reflectionMethod->getAttributes(),
                    function(array $accumulator, ReflectionAttribute $attribute) {
                        $accumulator[$attribute->getName()] = $attribute;
                        return $accumulator;
                    },
                    []
                );

                if (
                    array_key_exists(PathAttribute::class, $attributes)
                    && array_key_exists(MethodAttribute::class, $attributes)
                ) {
                    $path = $prefix.$attributes[PathAttribute::class]->getArguments()[0];

                    if (str_starts_with($path, "/") === false) {
                        $path = "/{$path}";
                    }

                    $name = array_key_exists(RouteNameAttribute::class, $attributes)
                        ? $attributes[RouteNameAttribute::class]
                        : null;

                    $routes[] = new Route(
                        $attributes[MethodAttribute::class]->getArguments(),
                        $path,
                        "{$controllerFQCN}::{$reflectionMethod->getName()}",
                        $name
                    );
                }
            }
        }

        if (
            $this->configuration["cache"]["enabled"]
            && is_writable(dirname($this->configuration["cache"]["tableCacheFilepath"]))
        ) {
            file_put_contents(
                $this->configuration["cache"]["tableCacheFilepath"],
                serialize($routes)
            );
        }

        return new RoutingTable($routes);
    }

    /**
     * Returns dispatch result for given `$request`.
     *
     * @param RequestInterface $request
     * @return AbstractResult
     * @throws \ReflectionException
     */
    public function dispatch(RequestInterface $request): AbstractResult
    {
        $function = "FastRoute\\simpleDispatcher";
        $params = [];

        if (
            $this->configuration["cache"]["enabled"] === true
            && is_string($this->configuration["cache"]["fastRouteCacheFilepath"])
            && is_writable(dirname($this->configuration["cache"]["fastRouteCacheFilepath"]))
        ) {
            $function = "FastRoute\\cachedDispatcher";
            $params = [
                "cacheFile" => $this->configuration["cache"]["fastRouteCacheFilepath"]
            ];
        }

        $routes = $this->buildRoutingTable();
        $fastRoute = $function(function(RouteCollector $r) use ($routes) {
            foreach ($routes as $routeIndex => $routeObject) {
                $r->addRoute($routeObject->allowedMethods, $routeObject->path, $routeIndex);
            }
        }, $params);

        $result = $fastRoute->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($result[0] === Dispatcher::NOT_FOUND) {
            return new NotFoundResult();
        }

        if ($result[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return new MethodNotAllowedResult($result[1]);
        }

        $route = $routes[$result[1]];
        return $route->setArguments($result[2]);
    }

    /**
     * Returns Wake default router default configuration.
     *
     * @return array
     */
    public static function getDefaultConfiguration(): array
    {
        return [
            "cache" => [
                "enabled"                   => false,
                "fastRouteCacheFilepath"    => null,
                "tableCacheFilepath"        => null
            ],
            "controllerFQCNArray" => [],
            "prefix" => null
        ];
    }
}
