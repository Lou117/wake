# Welcome to Wake PHP micro-framework!
**Wake** (for **W**eb-**A**pplication **KE**rnel) is an attempt to give PHP developers good foundations for their 
project, whatever its scope, size, type or lifespan. Relying on battle-proof libraries like FastRoute, Monolog and 
Guzzle, *Wake* only gives you foundations, to let **your** creativity do the magic with minimal impact on performances.

Wake is the assembly of several well-known libraries:
- [Guzzle PSR-7 implementation](https://github.com/guzzle/psr7) (also used by Guzzle HTTP client, AWS PHP SDK, or Slim framework);
- [FastRoute](https://github.com/nikic/FastRoute) (also used by Slim framework, Laravel Lumen framework or PHPMyAdmin project);
- [Monolog](https://github.com/Seldaek/monolog) (also used by Laravel and Symfony frameworks).

In addition to PSR-7, Wake uses PSR-15 (HTTP Server Request Handlers) and PSR-11 (Container Interface) Standards 
Recommendations, ensuring interoperability at most levels and smoothing learning curve for developers who want to give 
**Wake** its chance.

**Wake** has been written from the ground-up with PHP 8.1, ensuring the cleanest code, the best performance and a 
lasting support from PHP Foundation.

## Installation
```
composer require lou117/wake
```

## Documentation
- [Quickstart](docs/quickstart.md)
- [Configuration](docs/configuration.md)
- [About middlewares](docs/middlewares.md)
