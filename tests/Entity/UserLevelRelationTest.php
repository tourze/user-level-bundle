<?php

namespace UserLevelBundle\Tests\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;

class UserLevelRelationTest extends TestCase
{
    public function testGetId_whenNewInstance_returnsNull(): void
    {
        $relation = new UserLevelRelation();
        $this->assertNull($relation->getId());
    }

    public function testIsValid_withDefaultValue_returnsFalse(): void
    {
        $relation = new UserLevelRelation();
        $this->assertFalse($relation->isValid());
    }

    public function testSetValid_withTrue_storesTrue(): void
    {
        $relation = new UserLevelRelation();
        
        $relation->setValid(true);
        
        $this->assertTrue($relation->isValid());
    }

    public function testSetValid_withFalse_storesFalse(): void
    {
        $relation = new UserLevelRelation();
        $relation->setValid(true);
        
        $relation->setValid(false);
        
        $this->assertFalse($relation->isValid());
    }

    public function testSetLevel_withLevelObject_storesLevel(): void
    {
        $relation = new UserLevelRelation();
        $level = new Level();
        
        $relation->setLevel($level);
        
        $this->assertSame($level, $relation->getLevel());
    }

    public function testSetUser_withUserObject_storesUser(): void
    {
        $relation = new UserLevelRelation();
        $user = $this->getMockBuilder(TestUserInterface::class)
            ->getMock();
        
        $relation->setUser($user);
        
        $this->assertSame($user, $relation->getUser());
    }

    public function testSetCreateTime_withDateTime_storesCreateTime(): void
    {
        $relation = new UserLevelRelation();
        $datetime = new DateTimeImmutable();
        
        $relation->setCreateTime($datetime);
        
        $this->assertSame($datetime, $relation->getCreateTime());
    }

    public function testSetUpdateTime_withDateTime_storesUpdateTime(): void
    {
        $relation = new UserLevelRelation();
        $datetime = new DateTimeImmutable();
        
        $relation->setUpdateTime($datetime);
        
        $this->assertSame($datetime, $relation->getUpdateTime());
    }
}

/**
 * 测试用的UserInterface实现
 */
class TestUserInterface implements UserInterface
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