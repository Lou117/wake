<?php declare(strict_types=1);
namespace Lou117\Wake\Configuration;

use RuntimeException;

class JSONFileLoader extends AbstractLoader
{
    /**
     * @throws \JsonException
     */
    public static function load(string $configuration_filepath = ""): array
    {
        if (file_exists($configuration_filepath) === false) {
            throw new RuntimeException("Configuration file not found (searched for {$configuration_filepath})");
        }

        $jsonString = file_get_contents($configuration_filepath);
        $result = json_decode($jsonString, true, flags: JSON_THROW_ON_ERROR);

        if (is_array($result) === false) {
            throw new RuntimeException("Configuration file JSON content does not decode to an array");
        }

        return $result;
    }
}
