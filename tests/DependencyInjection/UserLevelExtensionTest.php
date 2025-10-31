<?php

namespace UserLevelBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use UserLevelBundle\DependencyInjection\UserLevelExtension;

/**
 * @internal
 */
#[CoversClass(UserLevelExtension::class)]
final class UserLevelExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testLoad(): void
    {
        $extension = new UserLevelExtension();

        // 验证扩展类可以被正确实例化
        $this->assertInstanceOf(UserLevelExtension::class, $extension);

        // 验证扩展的别名
        $this->assertEquals('user_level', $extension->getAlias());
    }
}
