<?php
namespace Lou117\Wake\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PathAttribute
{
    /**
     * @var string
     */
    protected readonly string $path;


    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
