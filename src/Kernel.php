<?php declare(strict_types=1);
namespace Lou117\Wake;

use LogicException;
use Monolog\Logger;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use GuzzleHttp\Psr7\ServerRequest;
use Lou117\Wake\Container\Container;
use Lou117\Wake\Dependency\Resolver;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Lou117\Wake\Configuration\Configuration;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel implements RequestHandlerInterface
{
    const CONTAINER_ID_CONFIGURATION = "wake_configuration";
    const CONTAINER_ID_LOGGER = "wake_logger";
    const CONTAINER_ID_REQUEST = "wake_request";


    /**
     * @var Container
     */
    protected Container $container;

    /**
     * @var int
     */
    protected int $middlewareSequenceIndex = 0;


    public function __construct()
    {
        $this->container = new Container();
        $this->container->set(self::CONTAINER_ID_CONFIGURATION, new Configuration());
    }

    /**
     * Returns Wake kernel configuration.
     *
     * @return Configuration
     * @throws \Lou117\Wake\Container\NotFoundException
     */
    public function getConfiguration(): Configuration
    {
        return $this->container->get(self::CONTAINER_ID_CONFIGURATION);
    }

    /**
     * Returns Wake kernel logger.
     *
     * @return Logger
     * @throws \Lou117\Wake\Container\NotFoundException
     */
    public function getLogger(): Logger
    {
        return $this->container->get(self::CONTAINER_ID_LOGGER);
    }

    /**
     * @throws \Lou117\Wake\Container\NotFoundException
     * @throws \ReflectionException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewareSequence = $this->getConfiguration()->getMiddlewareSequence();

        $middlewareFQCN = $middlewareSequence[$this->middlewareSequenceIndex];
        $this->middlewareSequenceIndex++;

        $reflectionClass = new ReflectionClass($middlewareFQCN);
        $reflectedConstructor = $reflectionClass->getConstructor();

        if ($reflectedConstructor instanceof ReflectionMethod) {
            $dependencyResolver = new Resolver($this->container);

            /**
             * @var MiddlewareInterface $middleware
             */
            $middleware = $reflectionClass->newInstance(...$dependencyResolver->resolve(
                $reflectedConstructor->getParameters()
            ));
        } else {
            $middleware = new $middlewareFQCN();
        }

        if (($middleware instanceof MiddlewareInterface) === false) {
            throw new LogicException("Middleware {$middlewareFQCN} must implements PSR-11 MiddlewareInterface");
        }

        return $middleware->process($request, $this);
    }

    /**
     * Loads given `$configuration_array` into Wake kernel configuration.
     *
     * @param array $configuration_array
     * @return $this
     * @throws \Lou117\Wake\Container\NotFoundException
     */
    public function loadConfiguration(array $configuration_array): self
    {
        $this->container->get(self::CONTAINER_ID_CONFIGURATION)->import($configuration_array);
        return $this;
    }

    /**
     * @param RequestInterface|null $request
     * @return ResponseInterface
     * @throws \Lou117\Wake\Container\NotFoundException
     * @throws \ReflectionException
     */
    public function run(RequestInterface $request = null): ResponseInterface
    {
        $this->setupLogger();

        if (is_null($request)) {
            $request = ServerRequest::fromGlobals();
        }

        $this->container->set(self::CONTAINER_ID_REQUEST, $request);

        if (empty($this->getConfiguration()->getMiddlewareSequence())) {
            throw new RuntimeException("Middleware sequence cannot be empty");
        }

        return $this->handle($request);
    }

    /**
     * Sets up Wake kernel logger.
     *
     * @return $this
     * @throws \Lou117\Wake\Container\NotFoundException
     */
    protected function setupLogger(): self
    {
        $configuration = $this->getConfiguration()->get(Configuration::DIRECTIVE_LOGGER);
        $this->container->set(self::CONTAINER_ID_LOGGER, new Logger($configuration["name"], [
            new $configuration["class"](...$configuration["params"])
        ]));

        return $this;
    }
}
