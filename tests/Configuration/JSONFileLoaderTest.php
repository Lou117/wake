<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Configuration\JSONFileLoader;

class JSONFileLoaderTest extends TestCase
{

    protected string $validJSONFilepath;

    protected string $invalidJSONFilepath;

    protected string $notArrayJSONFilepath;

    protected array $json = [
        "test" => "test"
    ];


    public function setUp(): void
    {
        $this->validJSONFilepath = tempnam(sys_get_temp_dir(), "phpunitjsonfileloadertest");
        file_put_contents($this->validJSONFilepath, json_encode([
            "test" => "test"
        ]));

        $this->invalidJSONFilepath = tempnam(sys_get_temp_dir(), "phpunitjsonfileloadertest");
        file_put_contents($this->invalidJSONFilepath, '["test" => "test"');

        $this->notArrayJSONFilepath = tempnam(sys_get_temp_dir(), "phpunitjsonfileloadertest");
        file_put_contents($this->notArrayJSONFilepath, "null");
    }

    public function tearDown(): void
    {
        unlink($this->validJSONFilepath);
        unlink($this->invalidJSONFilepath);
        unlink($this->notArrayJSONFilepath);
    }

    /**
     * @covers \Lou117\Wake\Configuration\JSONFileLoader::load
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testLoad()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertSame(JSONFileLoader::load($this->validJSONFilepath), $this->json);
    }

    /**
     * @covers \Lou117\Wake\Configuration\JSONFileLoader::load
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testLoadWithNotFoundFile()
    {
        $this->expectException(RuntimeException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        JSONFileLoader::load(uniqid());
    }

    /**
     * @covers \Lou117\Wake\Configuration\JSONFileLoader::load
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testLoadWithInvalidJSON()
    {
        $this->expectException(JsonException::class);
        JSONFileLoader::load($this->invalidJSONFilepath);
    }

    /**
     * @covers \Lou117\Wake\Configuration\JSONFileLoader::load
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testLoadWithNotArrayJSON()
    {
        $this->expectException(RuntimeException::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        JSONFileLoader::load($this->notArrayJSONFilepath);
    }
}
