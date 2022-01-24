<?php
use PHPUnit\Framework\TestCase;
use Lou117\Wake\Container\Container;
use Lou117\Wake\Dependency\Resolver;
use Lou117\Wake\Dependency\Provider;

class ResolverTest extends TestCase
{

    /**
     * @covers \Lou117\Wake\Dependency\Resolver::__construct
     * @covers \Lou117\Wake\Dependency\Resolver::resolve
     * @return void
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testResolve()
    {
        $container = new Container();
        $container->set("test", "test1");
        $instance = new Resolver($container);

        $function = function($test, $wake_dependency_provider, $wake_dependency_resolver) {
            return true;
        };

        /** @noinspection PhpUnhandledExceptionInspection */
        $reflectionFunction = new ReflectionFunction($function);
        $resolvedParameters = $instance->resolve($reflectionFunction->getParameters());

        $this->assertArrayHasKey("test", $resolvedParameters);
        $this->assertSame("test1", $resolvedParameters["test"]);

        $this->assertArrayHasKey("wake_dependency_provider", $resolvedParameters);
        $this->assertInstanceOf(Provider::class, $resolvedParameters["wake_dependency_provider"]);

        $this->assertArrayHasKey("wake_dependency_resolver", $resolvedParameters);
        $this->assertInstanceOf(Resolver::class, $resolvedParameters["wake_dependency_resolver"]);
    }
}
