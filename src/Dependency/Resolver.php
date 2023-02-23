<?php declare(strict_types=1);
namespace Lou117\Wake\Dependency;

use ReflectionParameter;
use Lou117\Wake\Container\Container;

class Resolver
{
    /**
     * @var Container
     */
    protected Container $container;


    /**
     * @param Container $wake_container
     */
    public function __construct(Container $wake_container)
    {
        $this->container = $wake_container;
    }

    /**
     * Returns an associative array where keys are parameter names and values are corresponding dependencies.
     *
     * @param ReflectionParameter[] $arguments
     * @return array
     * @throws \Lou117\Wake\Container\NotFoundException
     */
    public function resolve(array $arguments): array
    {
        $return = [];

        foreach ($arguments as $argument) {
            if ($this->container->has($argument->getName())) {
                $return[$argument->getName()] = $this->container->get($argument->getName());
                continue;
            }

            if ($argument->getName() === "wake_dependency_provider") {
                $return[$argument->getName()] = new Provider($this->container);
            }

            if ($argument->getName() === "wake_dependency_resolver") {
                $return[$argument->getName()] = $this;
            }
        }

        return $return;
    }
}
