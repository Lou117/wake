# Router
**Wake** comes with routing components taking advantage of [FastRoute](https://github.com/nikic/FastRoute) library, 
[PHP 8 attributes](https://www.php.net/releases/8.0/en.php#attributes) and 
[Wake dependencies management](./dependencies_management.md) components.

## Using Wake router
To use Wake router, you'll have to add `Middleware\RouterMiddleware` middleware class to your middleware sequence when 
[configuring Wake kernel](./configuration.md).
```php
return [
    "wake-mw-sequence" => [
        \Lou117\Wake\Middleware\RouterMiddleware::class
    ],
    ...
];
```

## Designating controllers
Wake router will scan for entry points in all classes you'll designate as "controllers". To designate classes as 
controllers, you'll have to add their FQCN (**F**ully **Q**ualified **C**lass **N**ame) to Wake configuration array 
under `wake-router.controllerFQCNArray` configuration directive.
```php
return [
    "wake-mw-sequence" => [
        \Lou117\Wake\Middleware\RouterMiddleware::class
    ],
    "wake-router" => [
        "controllerFQCNArray" => [
            \My\Controller::class
        ]
    ]
];
```

Wake router will use `Reflection` API to search for entry points: to designate a "controller" class method as an entry 
point, you'll have to add `MethodAttribute` and `PathAttribute` as method attributes:
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo')]
    public function getFoo()
    {
        return new Response();
    }
}
```

### Using `MethodAttribute`
Wake router `MethodAttribute` is used to define which HTTP method(s) is (are) allowed for a given entry points:
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET', 'POST')]
    #[\Lou117\Wake\Router\PathAttribute('/foo')]
    public function getFoo()
    {
        // Will be called when HTTP request matches either `GET /foo` or `POST /foo`
        return new Response();
    }
}
```

### Using `PathAttribute`
Wake router `PathAttribute` is used to define with path(s) should match with a given entry point. You can use 
[FastRoute documentation](https://github.com/nikic/FastRoute#defining-routes) for a complete view about path formatting.
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo/{id}')]
    public function getFoo()
    {
        return new Response();
    }
}
```

## Using dependency injection with controller class methods
You can use Wake dependencies management in your controller class methods:
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo')]
    public function getFoo(\Monolog\Logger $wake_logger)
    {
        // Will dump Wake logger instance
        var_dump($wake_logger);
        return new Response();
    }
}
```

## Using a global prefix for route paths
You can add a global prefix for all your route paths using `wake-router.prefix` configuration directive:
```php
return [
    "wake-mw-sequence" => [
        \Lou117\Wake\Middleware\RouterMiddleware::class
    ],
    "wake-router" => [
        "controllerFQCNArray" => [
            \My\Controller::class
        ]
        "prefix" => "/bar"
    ]
];
```
Now using our controller:
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo')]
    public function getFoo()
    {
        // Will be called HTTP request matches 'GET /bar/foo'
        return new Response();
    }
}
```

## Cache strategy
With your application growing bigger and bigger, you'll need to enable caching to avoid performance issues. When caching 
is enabled, Wake router will use caching at two levels:
- Controller classes scanning results (a.k.a. "routing table"), to avoid `Reflection` API being used with every single 
HTTP request;
- FastRoute built-in caching.
```php
return [
    "wake-mw-sequence" => [
        \Lou117\Wake\Middleware\RouterMiddleware::class
    ],
    "wake-router" => [
        "controllerFQCNArray" => [
            \My\Controller::class
        ],
        "cache" => [
            "enabled"                   => true,
            "fastRouteCacheFilepath"    => "/wherever/you/want/to/store/your/cache/file/for/FastRoute",
            "tableCacheFilepath"        => "/wherever/you/want/to/store/your/cache/file/for/WakeRoutingTable"
        ]
    ]
];
```
Please note that you're **not** required to provide both file paths: if one of them is `null`, Wake router will disable 
caching at corresponding level.

## Dependencies provided by Wake router
When using Wake router, you'll be provided with two injectable dependencies: `$wake_route` and `$wake_routing_table`.

### `$wake_route`
When using `$wake_route` as parameter name for a method that will take advantage of `Dependency\Resolver` class 
capabilities, you'll be provided with a `Router\Result\Route` instance.
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo/{id}')]
    public function getFoo(\Lou117\Wake\Router\Result\Route $wake_route)
    {
        // Will dump an array with a key named 'id' and a value corresponding to `{id}` in path
        var_dump($wake_route->getArguments());
        return new Response();
    }
}
```

### `$wake_routing_table`
When using `$wake_routing_table` as parameter name for a method that will take advantage of `Dependency\Resolver` class 
capabilities, you'll be provided with a `Router\RoutingTable` instance.
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo')]
    public function getFoo(\Lou117\Wake\Router\RoutingTable $wake_routing_table)
    {
        // Will dump Wake router `RoutingTable` instance
        var_dump($wake_route);
        return new Response();
    }
}
```

## Generating a URL using a route name
You can generate a URL by using `RoutingTable::generateURL()` method, but you'll need to provide a route name as a PHP 
attribute:
```php
class MyControllerClass
{
    #[\Lou117\Wake\Router\MethodAttribute('GET')]
    #[\Lou117\Wake\Router\PathAttribute('/foo/{id}')]
    #[\Lou117\Wake\Router\RouteNameAttribute('getfoo')]
    public function getFoo(\Lou117\Wake\Router\RoutingTable $wake_routing_table)
    {
        // Will dump a string: '/foo/1234'
        var_dump($wake_routing_table->generateURL("getfoo", [
            "id" => 1234
        ]));
        
        return new Response();
    }
}
```
Please note that you're not **required** to provide a route name for every entry point, but you cannot generate a URL 
without a route name, and this route name must be **unique**.
