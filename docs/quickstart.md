# Quickstart
[Elevator pitch](https://en.wikipedia.org/wiki/Elevator_pitch): **Wake** kernel receives a PSR-7 `RequestInterface` 
instance and returns a PSR-7 `ResponseInterface` instance.

Your project entry point (or "front-controller") will instantiate Wake `Kernel` class and call its `run` method:
```php
use Lou117\Wake\Kernel;
use Lou117\Wake\ResponseFactory;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Configuration\Configuration;
use Lou117\Wake\Configuration\JSONFileLoader;

require("./vendor/autoload.php");

$kernel = new Kernel();
$kernel->loadConfiguration([
    Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [
        Your\MagicMiddleware::class
    ]
]);
$response = $kernel->run();
ResponseFactory::sendToClient($response);
```

Let's walk over those few lines:
- `Kernel` class constructor does not accept any argument;
- Although **Wake** includes its own [default configuration](./configuration.md), you are **required** to provide at 
least a [middleware sequence](./middlewares.md) through configuration;
- `Kernel::run()` method accepts as single parameter an instance of any class implementing PSR-7 `RequestInterface`, 
**but** will build a PSR-7 `ServerRequest` instance from PHP globals if no parameter is provided (as it's the case 
here);
- You are free to do anything with `Kernel::run()` method return value: here `ResponseFactory` class (included with 
Wake) is used to send returned response (headers, then body if any) to client.
