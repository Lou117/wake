<?php declare(strict_types=1);
namespace Lou117\Wake\Router\Result;

class MethodNotAllowed extends AbstractResult
{
    /**
     * @var string[]
     */
    public readonly array $allowedMethods;


    /**
     * @param string[] $allowed_methods
     */
    public function __construct(array $allowed_methods)
    {
        $this->allowedMethods = $allowed_methods;
    }
}
