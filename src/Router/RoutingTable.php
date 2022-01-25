<?php
namespace Lou117\Wake\Router;

use Iterator;
use Countable;
use ArrayAccess;
use RuntimeException;
use InvalidArgumentException;
use Lou117\Wake\Router\Result\Route;

class RoutingTable implements Iterator, Countable, ArrayAccess
{
    /**
     * @var bool
     */
    public readonly bool $fromCache;

    /**
     * @var int
     */
    protected int $index = 0;

    /**
     * @var Route[]
     */
    protected array $routes = [];


    /**
     * @param Route[] $routes
     */
    public function __construct(array $routes, bool $from_cache = false)
    {
        $this->routes = array_values(array_filter($routes, function($route) {
            return $route instanceof Route;
        }));

        $this->fromCache = $from_cache;
    }

    public function count(): int
    {
        return count($this->routes);
    }

    public function current(): Route|bool
    {
        return $this->offsetGet($this->index);
    }

    /**
     * Generates a path string corresponding to `$route_name` path, with route arguments replaced with given
     * `$arguments`.
     *
     * @param string $route_name
     * @param array $arguments
     * @return string
     * @throws InvalidArgumentException - if no route matches with given `$route_name`.
     * @throws RuntimeException - if multiple routes matches with given `$route_name`.
     */
    public function generateURL(string $route_name, array $arguments = []): string
    {
        $routes = [];

        foreach ($this->routes as $route) {
            if ($route->name === $route_name) {
                $routes[] = $route;
            }
        }

        if (empty($route)) {
            throw new InvalidArgumentException("Route with name <{$route_name}> not found");
        }

        if (count($routes) > 1) {
            throw new RuntimeException("Route name <{$route_name}> is not unique");
        }

        $return = $routes[0]->path;

        foreach ($arguments as $key => $value) {
            $return = preg_replace('#{'.$key.'(.*)}#', $value, $return);
        }

        return $return;
    }

    public function key(): int|null
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->routes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->routes[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (($value instanceof Route) === false) {
            throw new InvalidArgumentException("Only Route instances can be set");
        }

        is_null($offset)
            ? $this->routes[] = $value
            : $this->routes[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->routes[$offset]);
    }

    public function rewind(): void
    {
        $this->index = 0;
        // In case routes have been unset during last iteration
        $this->routes = array_values($this->routes);
    }

    public function valid(): bool
    {
        return $this->offsetExists($this->index);
    }
}
