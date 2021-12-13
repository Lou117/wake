<?php
namespace Lou117\Wake;

use LogicException;
use Monolog\Logger;
use ReflectionClass;
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
    protected function getConfiguration(): Configuration
    {
        return $this->container->get(self::CONTAINER_ID_CONFIGURATION);
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
        $reflectionMethod = $reflectionClass->getConstructor();
        $dependencyResolver = new Resolver($this->container);

        /**
         * @var $middleware MiddlewareInterface
         */
        $middleware = $reflectionClass->newInstance(...$dependencyResolver->resolve(
            $reflectionMethod->getParameters()
        ));

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
