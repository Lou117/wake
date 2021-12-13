<?php
namespace Lou117\Wake\Router\Result;

class Route extends AbstractResult
{
    /**
     * @var array
     */
    public readonly array $allowedMethods;

    /**
     * @var array
     */
    protected array $arguments = [];

    /**
     * @var string
     */
    public readonly string $controller;

    /**
     * @var string
     */
    public readonly string $path;


    /**
     * @param array $allowed_methods
     * @param string $path
     * @param string $controller
     */
    public function __construct(array $allowed_methods, string $path, string $controller)
    {
        $this->allowedMethods = $allowed_methods;
        $this->controller = $controller;
        $this->path = $path;
    }

    /**
     * Returns this route's arguments.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Merges given `$arguments` with this route's arguments.
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = array_replace_recursive($this->arguments, $arguments);
        return $this;
    }
}
