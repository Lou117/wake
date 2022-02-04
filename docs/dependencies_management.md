# Integrated dependencies management
**Wake** comes with dependencies management components, allowing dependency injection and providing throughout request 
handling process.

## Storing dependencies
Dependencies are stored within Wake kernel integrated container, which complies with 
[PSR-11](https://www.php-fig.org/psr/psr-11/) standard recommendation. Wake kernel is built in a way you can't interact 
directly with this container: instead, you use dependencies management classes.

**Injectable dependencies** are designated using their identifier (see PSR-11 title 1.1.1 - "Entry identifiers") in Wake 
kernel integrated container.

## Requesting dependencies from Wake kernel
When building [middlewares](./middlewares.md) for your project, you can request dependency injection using class 
constructor: if any **parameter name** of your middleware constructor matches with an injectable dependency, your 
constructor method will be provided with this dependency.

```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class YourMiddleware implements MiddlewareInterface
{
    protected $dependency;

    public function __construct($your_dependency)
    {
        // Assuming Wake kernel integrated container contains a value for 'your_dependency' identifier:
        $this->dependency = $your_dependency;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // your middleware logic
    }
}
```

## Using dependency injection *within* your middlewares
You can use `$wake_dependency_resolver` as a "special" parameter name to receive an instance of `Dependency\Resolver` 
class as one of your middleware constructor parameter, and use Wake dependencies management for your own needs in your 
middleware `process` method:
```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lou117\Wake\Dependency\Resolver as DependencyResolver;

class YourMiddleware extends MiddlewareInterface
{
    protected DependencyResolver $dependencyResolver;


    public function __construct(DependencyResolver $wake_dependency_resolver)
    {
        $this->dependencyResolver = $wake_dependency_resolver;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $reflectedClass = new ReflectionClass($this);
        $reflectedMethod = $reflectedClass->getMethod("foo");
        $this->foo($this->dependencyResolver->resolve($reflectedMethod->getParameters()));
        return $handler->handle($request);
    }
    
    protected function foo(DependencyResolver $wake_dependency_resolver): void
    {
        // Will dump an instance of Dependency\Resolver class
        var_dump($wake_dependency_resolver);
    }
}
```
Of course, for sake of brevity, the example above is not very realistic. But it gives you good sense of how to use Wake 
dependency resolver.

## Providing dependencies
You can provide dependencies using `Dependency\Provider` class, which can be injected as a dependency of your 
middleware(s) using "special" parameter name `$wake_dependency_provider`.
```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lou117\Wake\Dependency\Provider as DependencyProvider;

class YourMiddleware extends MiddlewareInterface
{
    protected DependencyProvider $dependencyProvider;


    public function __construct(DependencyProvider $wake_dependency_provider)
    {
        $this->dependencyProvider = $wake_dependency_provider;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $this->dependencyProvider->provide("my_dependency", "foobar");
        // You can now use `$my_dependency` as a parameter name wherever you need dependency injection
        return $handler->handle($request);
    }
}
```

## Other "special" parameter names
### `$wake_configuration`
When using `$wake_configuration` as parameter name for a method that will take advantage of `Dependency\Resolver` class 
capabilities, you'll be provided with current Wake configuration array.
```php
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class YourMiddleware extends MiddlewareInterface
{
    protected array $wakeConfiguration;


    public function __construct(array $wake_configuration)
    {
        $this->wakeConfiguration = $wake_configuration;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // Will dump current Wake configuration
        var_dump($this->wakeConfiguration);
        return $handler->handle($request);
    }
}
```

### `$wake_logger`
When using `$wake_logger` as parameter name for a method that will take advantage of `Dependency\Resolver` class 
capabilities, you'll be provided with Wake kernel logger.
```php
use Monolog\Logger;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class YourMiddleware extends MiddlewareInterface
{
    protected Logger $wakeLogger;


    public function __construct(array $wake_logger)
    {
        $this->wakeLogger = $wake_logger;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        // Will dump Wake logger instance
        var_dump($this->wakeLogger);
        return $handler->handle($request);
    }
}
```
