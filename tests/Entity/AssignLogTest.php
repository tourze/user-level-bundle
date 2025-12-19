<?php

namespace UserLevelBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Entity\Level;

/**
 * @internal
 */
#[CoversClass(AssignLog::class)]
final class AssignLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new AssignLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'type' => ['type', 123],
            'remark' => ['remark', 'test_value'],
        ];
    }

    public function testGetIdWhenNewInstanceReturnsNull(): void
    {
        $log = new AssignLog();
        $this->assertNull($log->getId());
    }

    public function testSetNewLevelWithLevelObjectStoresNewLevel(): void
    {
        $log = new AssignLog();
        $level = new Level();
        $level->setLevel(2);
        $level->setTitle('VIP2');
        $level->setValid(true);

        $log->setNewLevel($level);

        $this->assertSame($level, $log->getNewLevel());
    }

    public function testSetOldLevelWithLevelObjectStoresOldLevel(): void
    {
        $log = new AssignLog();
        $level = new Level();
        $level->setLevel(1);
        $level->setTitle('VIP1');
        $level->setValid(true);

        $log->setOldLevel($level);

        $this->assertSame($level, $log->getOldLevel());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $log = new AssignLog();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'testuser';
            }
        };

        $log->setUser($user);

        $this->assertSame($user, $log->getUser());
    }

    public function testSetTypeWithValidIntegerStoresType(): void
    {
        $log = new AssignLog();
        $type = 1; // 升级
        $log->setType($type);
        $this->assertSame($type, $log->getType());
    }

    public function testSetAssignTimeWithDateTimeStoresAssignTime(): void
    {
        $log = new AssignLog();
        $datetime = new \DateTimeImmutable();
        $log->setAssignTime($datetime);
        $this->assertSame($datetime, $log->getAssignTime());
    }

    public function testSetRemarkWithValidStringStoresRemark(): void
    {
        $log = new AssignLog();
        $remark = '系统自动升级';
        $log->setRemark($remark);
        $this->assertSame($remark, $log->getRemark());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $log = new AssignLog();
        $createdBy = 'admin';
        $log->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $log->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $log = new AssignLog();
        $updatedBy = 'admin';
        $log->setUpdatedBy($updatedBy);
        $this->assertSame($updatedBy, $log->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $log = new AssignLog();
        $datetime = new \DateTimeImmutable();
        $log->setCreateTime($datetime);
        $this->assertSame($datetime, $log->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $log = new AssignLog();
        $datetime = new \DateTimeImmutable();
        $log->setUpdateTime($datetime);
        $this->assertSame($datetime, $log->getUpdateTime());
    }

    public function testRetrieveAdminArrayReturnsExpectedArray(): void
    {
        $log = new AssignLog();

        $newLevel = new Level();
        $newLevel->setLevel(2);
        $newLevel->setTitle('VIP2');
        $newLevel->setValid(true);

        $oldLevel = new Level();
        $oldLevel->setLevel(1);
        $oldLevel->setTitle('VIP1');
        $oldLevel->setValid(true);

        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'testuser';
            }

            public function getId(): string
            {
                return '123';
            }

            public function getNickName(): string
            {
                return '测试用户';
            }
        };

        $log->setNewLevel($newLevel);
        $log->setOldLevel($oldLevel);
        $log->setUser($user);
        $log->setAssignTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $log->setCreateTime(new \DateTimeImmutable('2023-01-01 10:00:01'));

        $result = $log->retrieveAdminArray();

        $this->assertArrayHasKey('newLevelInfo', $result);
        $this->assertArrayHasKey('oldLevelInfo', $result);
        $this->assertArrayHasKey('userInfo', $result);
        $this->assertArrayHasKey('assignTime', $result);
        $this->assertArrayHasKey('createTime', $result);

        $this->assertEquals('2023-01-01 10:00:00', $result['assignTime']);
        $this->assertEquals('2023-01-01 10:00:01', $result['createTime']);
        $this->assertEquals(['id' => null, 'level' => 2, 'title' => 'VIP2'], $result['newLevelInfo']);
        $this->assertEquals(['id' => null, 'level' => 1, 'title' => 'VIP1'], $result['oldLevelInfo']);
        $this->assertEquals([
            'id' => '123',
            'nickName' => '测试用户',
            'username' => 'testuser',
        ], $result['userInfo']);
    }
}
