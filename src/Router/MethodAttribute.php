<?php
namespace Lou117\Wake\Router;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_METHOD)]
class MethodAttribute
{
    const METHOD_DELETE = "DELETE";
    const METHOD_GET = "GET";
    const METHOD_POST = "POST";
    const METHOD_PUT = "PUT";


    /**
     * @var array
     */
    public readonly array $methods;

    /**
     * @param string ...$args
     */
    public function __construct(...$args)
    {
        foreach ($args as $method) {
            if (in_array($method, [
                self::METHOD_DELETE,
                self::METHOD_GET,
                self::METHOD_POST,
                self::METHOD_PUT
            ]) === false) {
                throw new InvalidArgumentException("Invalid method, must be one of MethodAttribute::METHOD_* constants");
            }
        }

        $this->methods = $args;
    }
}
