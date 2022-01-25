<?php
namespace Lou117\Wake\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteNameAttribute
{
    /**
     * @var string
     */
    protected readonly string $routeName;


    /**
     * @param string $route_name
     */
    public function __construct(string $route_name)
    {
        $this->routeName = $route_name;
    }
}
