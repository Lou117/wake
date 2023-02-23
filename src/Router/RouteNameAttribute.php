<?php declare(strict_types=1);
namespace Lou117\Wake\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class RouteNameAttribute
{
    /**
     * @var string
     */
    public string $routeName;


    /**
     * @param string $route_name
     */
    public function __construct(string $route_name)
    {
        $this->routeName = $route_name;
    }
}
