<?php
namespace Lou117\Wake\Configuration;

use Monolog\Handler\RotatingFileHandler;

class Configuration
{
    const DEFAULT_LOGGER_NAME = "wake";
    const DIRECTIVE_LOGGER = "wake-logger";
    const DIRECTIVE_MIDDLEWARE_SEQUENCE = "wake-mw-sequence";


    /**
     * @var array
     */
    protected array $configuration = [];


    public function __construct()
    {
        $this->configuration = self::getDefaultConfiguration();
    }


    /**
     * Returns value associated with given `$configuration_directive`.
     *
     * @param string $configuration_directive
     * @return mixed
     */
    public function get(string $configuration_directive): mixed
    {
        if (array_key_exists($configuration_directive, $this->configuration)) {
            return $this->configuration[$configuration_directive];
        }

        // handle JS notation
        return null;
    }

    /**
     * Returns Wake kernel middleware sequence.
     *
     * @return array
     */
    public function getMiddlewareSequence(): array
    {
        return $this->get(self::DIRECTIVE_MIDDLEWARE_SEQUENCE);
    }

    /**
     * Returns Wake kernel default configuration.
     *
     * @return array
     */
    public static function getDefaultConfiguration(): array
    {
        $tmpDir = sys_get_temp_dir();

        if (substr($tmpDir, 0, -1) !== DIRECTORY_SEPARATOR) {
            $tmpDir .= DIRECTORY_SEPARATOR;
        }

        return [
            self::DIRECTIVE_MIDDLEWARE_SEQUENCE => [],
            self::DIRECTIVE_LOGGER => [
                "name"      => self::DEFAULT_LOGGER_NAME,
                "class"     => RotatingFileHandler::class,
                "params"    => [$tmpDir.self::DEFAULT_LOGGER_NAME, 10]
            ]
        ];
    }

    /**
     * Imports given `$configuration_array` into current configuration.
     *
     * @param array $configuration_array
     * @return $this
     */
    public function import(array $configuration_array): self
    {
        $this->configuration = array_replace_recursive($this->configuration, $configuration_array);
        $this->configuration[self::DIRECTIVE_MIDDLEWARE_SEQUENCE] = array_values($this->configuration[self::DIRECTIVE_MIDDLEWARE_SEQUENCE]);
        return $this;
    }
}
