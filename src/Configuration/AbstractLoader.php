<?php
namespace Lou117\Wake\Configuration;

abstract class AbstractLoader
{
    /**
     * @return array
     */
    abstract public static function load(): array;
}
