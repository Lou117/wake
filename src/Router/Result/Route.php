<?php
namespace Lou117\Wake\Router;

class Route
{
    /**
     * @var array
     */
    public readonly array $allowedMethods;

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
}
