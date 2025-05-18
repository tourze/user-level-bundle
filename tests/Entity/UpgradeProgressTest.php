<?php

namespace UserLevelBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;

class UpgradeProgressTest extends TestCase
{
    public function testGetId_whenNewInstance_returnsNull(): void
    {
        $progress = new UpgradeProgress();
        $this->assertNull($progress->getId());
    }

    public function testSetUser_withUserObject_storesUser(): void
    {
        $progress = new UpgradeProgress();
        $user = $this->getMockBuilder(ProgressTestUserInterface::class)
            ->getMock();
        
        $progress->setUser($user);
        
        $this->assertSame($user, $progress->getUser());
    }

    public function testSetUpgradeRule_withRuleObject_storesRule(): void
    {
        $progress = new UpgradeProgress();
        $rule = new UpgradeRule();
        
        $progress->setUpgradeRule($rule);
        
        $this->assertSame($rule, $progress->getUpgradeRule());
    }

    public function testSetValue_withInteger_storesValue(): void
    {
        $progress = new UpgradeProgress();
        $value = 5000;
        
        $progress->setValue($value);
        
        $this->assertSame($value, $progress->getValue());
    }

    public function testSetValue_withNull_storesNull(): void
    {
        $progress = new UpgradeProgress();
        $progress->setValue(100);
        
        $progress->setValue(null);
        
        $this->assertNull($progress->getValue());
    }

    public function testSetCreateTime_withDateTime_storesCreateTime(): void
    {
        $progress = new UpgradeProgress();
        $datetime = new DateTimeImmutable();
        
        $progress->setCreateTime($datetime);
        
        $this->assertSame($datetime, $progress->getCreateTime());
    }

    public function testSetUpdateTime_withDateTime_storesUpdateTime(): void
    {
        $progress = new UpgradeProgress();
        $datetime = new DateTimeImmutable();
        
        $progress->setUpdateTime($datetime);
        
        $this->assertSame($datetime, $progress->getUpdateTime());
    }
}

/**
 * 测试用的UserInterface实现
 */
class ProgressTestUserInterface implements UserInterface
{
    public function getRoles(): array
    {
        return [];
    }
    
    public function eraseCredentials(): void
    {
    }
    
    public function getUserIdentifier(): string
    {
        return '';
    }
} 