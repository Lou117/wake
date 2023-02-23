<?php declare(strict_types=1);
namespace Lou117\Wake\Router;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class PathAttribute
{
    /**
     * @var string
     */
    public string $path;


    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }
}
