# Configuration
## Default configuration
**Wake** comes with its own default configuration, that can be overridden and augmented following your own needs:
```php
$wakeDefaultConfiguration = [
    Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [],
    Configuration::DIRECTIVE_LOGGER => [
        "name"      => Configuration::DEFAULT_LOGGER_NAME,
        "class"     => RotatingFileHandler::class,
        "params"    => ["/your/tmp/dir/".Configuration::DEFAULT_LOGGER_NAME, 10]
    ]
];
```
You can fetch **Wake** default configuration by statically calling `Configuration::getDefaultConfiguration()` method:
```php
use Lou117\Wake\Configuration\Configuration;

var_dump(Configuration::getDefaultConfiguration());
```

## Wake kernel configuration directives
As you can see in the example above, `Configuration` class has constants to ensure that you'll be using the right 
directives when configuring Wake kernel. This scheme is reproduced with Wake [router](./router.md), and will come handy 
when using PHP files as configuration files.

**BUT**: in case you choose JSON files as configuration files, here are constants values:
```
const DIRECTIVE_LOGGER = "wake-logger";
const DIRECTIVE_MIDDLEWARE_SEQUENCE = "wake-mw-sequence";
```

## Configuration loaders
Although `Kernel::loadConfiguration()` method accepts an array as single parameter, Wake come with some handy loaders 
for configuration files:
### `JSONFileLoader`
```php
use Lou117\Wake\Configuration\JSONFileLoader;

// ...

$kernel->loadConfiguration(JSONFileLoader::load("/your/json/configuration.json"));
```
### `PHPFileLoader`
```php
use Lou117\Wake\Configuration\PHPFileLoader;

// ...

$kernel->loadConfiguration(PHPFileLoader::load("/your/configuration/file/returning/php/array.php"));
```
Please note that when using `PHPFileLoader`, the file will be `require()`-d, and must return a PHP array:
```php
return [];
```
