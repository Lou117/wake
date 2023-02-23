<?php declare(strict_types=1);
namespace Lou117\Wake\Configuration;

abstract class AbstractLoader
{
    /**
     * @return array
     */
    abstract public static function load(): array;
}
