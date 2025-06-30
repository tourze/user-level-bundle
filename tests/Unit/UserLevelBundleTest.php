<?php

namespace UserLevelBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UserLevelBundle\UserLevelBundle;

class UserLevelBundleTest extends TestCase
{
    public function testGetBundleDependencies(): void
    {
        $dependencies = UserLevelBundle::getBundleDependencies();
        
        $this->assertEmpty($dependencies);
    }
    
    public function testBundleInstantiation(): void
    {
        $bundle = new UserLevelBundle();
        
        $this->assertInstanceOf(UserLevelBundle::class, $bundle);
    }
}