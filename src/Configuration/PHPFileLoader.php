<?php declare(strict_types=1);
namespace Lou117\Wake\Configuration;

use RuntimeException;

class PHPFileLoader extends AbstractLoader
{
    public static function load(string $configuration_filepath = ""): array
    {
        if (file_exists($configuration_filepath) === false) {
            throw new RuntimeException("Configuration file not found (searched for {$configuration_filepath})");
        }

        $returned = require($configuration_filepath);

        if (is_array($returned) === false) {
            throw new RuntimeException("Configuration file must return an array");
        }

        return $returned;
    }
}
