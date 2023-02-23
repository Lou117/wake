<?php declare(strict_types=1);
namespace Lou117\Wake\Dependency;

use Lou117\Wake\Container\Container;

class Provider
{
    /**
     * @var Container
     */
    protected Container $container;


    public function __construct(Container $wake_container)
    {
        $this->container = $wake_container;
    }

    /**
     * Adds given `$dependency` under given `$dependency_name` to Wake dependency injection system.
     *
     * @param string $dependency_name
     * @param mixed $dependency
     * @return $this
     */
    public function provide(string $dependency_name, mixed $dependency): self
    {
        $this->container->set($dependency_name, $dependency);
        return $this;
    }
}
