<?php

namespace UserLevelBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use UserLevelBundle\DependencyInjection\UserLevelExtension;

class UserLevelExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new UserLevelExtension();
        $container = new ContainerBuilder();
        
        $extension->load([], $container);
        
        // 验证资源被正确加载（由于是按命名空间自动配置，我们检查配置是否生效）
        $definitions = $container->getDefinitions();
        // 这个测试主要确保 load 方法不会抛出异常，并且至少有一些服务被注册
        $this->assertNotEmpty($definitions);
    }
}