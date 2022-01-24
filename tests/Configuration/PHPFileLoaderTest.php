<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Configuration\PHPFileLoader;

class PHPFileLoaderTest extends TestCase
{
    protected string $validFilepath;

    protected string $invalidFilepath;


    public function setUp(): void
    {
        $this->validFilepath = tempnam(sys_get_temp_dir(), "phpunitphpfileloadertest");
        file_put_contents($this->validFilepath, '<?php return ["test" => "test"]; ?>');

        $this->invalidFilepath = tempnam(sys_get_temp_dir(), "phpunitphpfileloadertest");
        file_put_contents($this->invalidFilepath, '<?php return "test"; ?>');
    }

    public function tearDown(): void
    {
        unlink($this->validFilepath);
        unlink($this->invalidFilepath);
    }

    /**
     * @covers \Lou117\Wake\Configuration\PHPFileLoader::load
     * @return void
     */
    public function testLoad()
    {
        $this->assertSame([
            "test" => "test"
        ], PHPFileLoader::load($this->validFilepath));
    }

    /**
     * @covers \Lou117\Wake\Configuration\PHPFileLoader::load
     * @return void
     */
    public function testLoadWithNotFoundFile()
    {
        $this->expectException(RuntimeException::class);
        PHPFileLoader::load(uniqid());
    }

    /**
     * @covers \Lou117\Wake\Configuration\PHPFileLoader::load
     * @return void
     */
    public function testLoadWithInvalidPHP()
    {
        $this->expectException(RuntimeException::class);
        PHPFileLoader::load($this->invalidFilepath);
    }
}
