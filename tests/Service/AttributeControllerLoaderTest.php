<?php

namespace UserLevelBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use UserLevelBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testSupportsReturnsFalse(): void
    {
        $this->assertFalse($this->loader->supports('resource'));
        $this->assertFalse($this->loader->supports('resource', 'type'));
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $collection = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testLoadCallsAutoload(): void
    {
        $collection = $this->loader->load('resource');

        $this->assertInstanceOf(RouteCollection::class, $collection);
    }
}
