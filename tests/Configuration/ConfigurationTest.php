<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Configuration\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @param array $array
     * @param string $prefix
     * @return array
     */
    protected function flatten(array $array, string $prefix = ""): array
    {
        $return = [];

        foreach ($array as $key => $value) {
            if (empty($prefix) === false) {
                $key = "{$prefix}.{$key}";
            }

            $return[$key] = $value;

            if (is_array($value)) {
                $return = array_merge($return, $this->flatten($value, $key));
            }
        }

        return $return;
    }

    /**
     * @covers \Lou117\Wake\Configuration\Configuration::__construct
     * @covers \Lou117\Wake\Configuration\Configuration::get
     * @covers \Lou117\Wake\Configuration\Configuration::getDefaultConfiguration
     * @return void
     */
    public function testDefaultConfigurationOnInstantiation()
    {
        $instance = new Configuration();
        $flat = $this->flatten(Configuration::getDefaultConfiguration());

        foreach ($flat as $key => $value) {
            $this->assertEquals($value, $instance->get($key));
        }
    }

    /**
     * @covers \Lou117\Wake\Configuration\Configuration::getMiddlewareSequence
     * @return void
     */
    public function testGetMiddlewareSequence()
    {
        $instance = new Configuration();
        $this->assertSame(
            $instance->get(Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE),
            $instance->getMiddlewareSequence()
        );
    }

    /**
     * @covers \Lou117\Wake\Configuration\Configuration::import
     * @return void
     */
    public function testImport()
    {
        $instance = new Configuration();
        $tmpLoggerConfiguration = $instance->get(Configuration::DIRECTIVE_LOGGER);

        $instance->import([
            "test" => "test",
            Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE => [
                "test"
            ]
        ]);

        $this->assertEquals("test", $instance->get("test"));
        $this->assertSame(
            ["test"],
            $instance->get(Configuration::DIRECTIVE_MIDDLEWARE_SEQUENCE)
        );
        $this->assertSame($tmpLoggerConfiguration, $instance->get(Configuration::DIRECTIVE_LOGGER));
    }
}
