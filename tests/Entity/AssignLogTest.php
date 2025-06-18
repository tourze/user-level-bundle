<?php

namespace UserLevelBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Entity\Level;

class AssignLogTest extends TestCase
{
    public function testGetId_whenNewInstance_returnsNull(): void
    {
        $log = new AssignLog();
        $this->assertNull($log->getId());
    }

    public function testSetNewLevel_withLevelObject_storesNewLevel(): void
    {
        $log = new AssignLog();
        $level = new Level();
        
        $log->setNewLevel($level);
        
        $this->assertSame($level, $log->getNewLevel());
    }

    public function testSetOldLevel_withLevelObject_storesOldLevel(): void
    {
        $log = new AssignLog();
        $level = new Level();
        
        $log->setOldLevel($level);
        
        $this->assertSame($level, $log->getOldLevel());
    }

    public function testSetUser_withUserObject_storesUser(): void
    {
        $log = new AssignLog();
        $user = $this->getMockForAbstractClass(UserInterface::class);
        
        $log->setUser($user);
        
        $this->assertSame($user, $log->getUser());
    }

    public function testSetType_withValidInteger_storesType(): void
    {
        $log = new AssignLog();
        $type = 1; // 升级
        
        $log->setType($type);
        
        $this->assertSame($type, $log->getType());
    }

    public function testSetAssignTime_withDateTime_storesAssignTime(): void
    {
        $log = new AssignLog();
        $datetime = new DateTimeImmutable();
        
        $log->setAssignTime($datetime);
        
        $this->assertSame($datetime, $log->getAssignTime());
    }

    public function testSetRemark_withValidString_storesRemark(): void
    {
        $log = new AssignLog();
        $remark = '系统自动升级';
        
        $log->setRemark($remark);
        
        $this->assertSame($remark, $log->getRemark());
    }

    public function testSetCreatedBy_withValidString_storesCreatedBy(): void
    {
        $log = new AssignLog();
        $createdBy = 'admin';
        
        $log->setCreatedBy($createdBy);
        
        $this->assertSame($createdBy, $log->getCreatedBy());
    }

    public function testSetUpdatedBy_withValidString_storesUpdatedBy(): void
    {
        $log = new AssignLog();
        $updatedBy = 'admin';
        
        $log->setUpdatedBy($updatedBy);
        
        $this->assertSame($updatedBy, $log->getUpdatedBy());
    }

    public function testSetCreateTime_withDateTime_storesCreateTime(): void
    {
        $log = new AssignLog();
        $datetime = new DateTimeImmutable();
        
        $log->setCreateTime($datetime);
        
        $this->assertSame($datetime, $log->getCreateTime());
    }

    public function testSetUpdateTime_withDateTime_storesUpdateTime(): void
    {
        $log = new AssignLog();
        $datetime = new DateTimeImmutable();
        
        $log->setUpdateTime($datetime);
        
        $this->assertSame($datetime, $log->getUpdateTime());
    }

    public function testRetrieveAdminArray_returnsExpectedArray(): void
    {
        $log = new AssignLog();
        
        // Mock dependencies
        $newLevel = $this->createMock(Level::class);
        $newLevel->method('retrieveAdminArray')->willReturn(['id' => 1, 'level' => 2, 'title' => 'VIP2']);
        
        $oldLevel = $this->createMock(Level::class);
        $oldLevel->method('retrieveAdminArray')->willReturn(['id' => 2, 'level' => 1, 'title' => 'VIP1']);
        
        // 创建一个带额外方法的 UserInterface 实现
        $user = $this->getMockBuilder(UserTestDouble::class)
            ->getMock();
            
        $user->method('getId')->willReturn('123');
        $user->method('getNickName')->willReturn('测试用户');
        $user->method('getUsername')->willReturn('testuser');
        $user->method('getUserIdentifier')->willReturn('testuser');
        $user->method('getRoles')->willReturn(['ROLE_USER']);
        
        $assignTime = new DateTimeImmutable('2023-01-01 10:00:00');
        $createTime = new DateTimeImmutable('2023-01-01 10:00:01');
        
        $log->setNewLevel($newLevel);
        $log->setOldLevel($oldLevel);
        $log->setUser($user);
        $log->setAssignTime($assignTime);
        $log->setCreateTime($createTime);
        
        $result = $log->retrieveAdminArray();
        $this->assertArrayHasKey('newLevelInfo', $result);
        $this->assertArrayHasKey('oldLevelInfo', $result);
        $this->assertArrayHasKey('userInfo', $result);
        $this->assertArrayHasKey('assignTime', $result);
        $this->assertArrayHasKey('createTime', $result);
        
        $this->assertEquals('2023-01-01 10:00:00', $result['assignTime']);
        $this->assertEquals('2023-01-01 10:00:01', $result['createTime']);
        $this->assertEquals(['id' => 1, 'level' => 2, 'title' => 'VIP2'], $result['newLevelInfo']);
        $this->assertEquals(['id' => 2, 'level' => 1, 'title' => 'VIP1'], $result['oldLevelInfo']);
        $this->assertEquals([
            'id' => '123',
            'nickName' => '测试用户',
            'username' => 'testuser',
        ], $result['userInfo']);
    }
}

/**
 * 用于测试的 UserInterface 实现
 */
class UserTestDouble implements UserInterface
{
    public function getId(): string
    {
        return '';
    }
    
    public function getNickName(): string
    {
        return '';
    }
    
    public function getUsername(): string
    {
        return '';
    }
    
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