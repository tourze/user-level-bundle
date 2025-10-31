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
        $log = $this->createMock(AssignLog::class);
        $log->method('getId')->willReturn(null);
        $this->assertNull($log->getId());
    }

    public function testSetNewLevelWithLevelObjectStoresNewLevel(): void
    {
        $log = $this->createMock(AssignLog::class);
        $level = $this->createMock(Level::class);

        $log->method('getNewLevel')->willReturn($level);
        $log->setNewLevel($level);

        $this->assertSame($level, $log->getNewLevel());
    }

    public function testSetOldLevelWithLevelObjectStoresOldLevel(): void
    {
        $log = $this->createMock(AssignLog::class);
        $level = $this->createMock(Level::class);

        $log->method('getOldLevel')->willReturn($level);
        $log->setOldLevel($level);

        $this->assertSame($level, $log->getOldLevel());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $log = $this->createMock(AssignLog::class);
        $user = $this->createMock(UserInterface::class);

        $log->method('getUser')->willReturn($user);
        $log->setUser($user);

        $this->assertSame($user, $log->getUser());
    }

    public function testSetTypeWithValidIntegerStoresType(): void
    {
        $log = $this->createMock(AssignLog::class);
        $type = 1; // 升级

        $log->method('getType')->willReturn($type);
        $log->setType($type);

        $this->assertSame($type, $log->getType());
    }

    public function testSetAssignTimeWithDateTimeStoresAssignTime(): void
    {
        $log = $this->createMock(AssignLog::class);
        $datetime = new \DateTimeImmutable();

        $log->method('getAssignTime')->willReturn($datetime);
        $log->setAssignTime($datetime);

        $this->assertSame($datetime, $log->getAssignTime());
    }

    public function testSetRemarkWithValidStringStoresRemark(): void
    {
        $log = $this->createMock(AssignLog::class);
        $remark = '系统自动升级';

        $log->method('getRemark')->willReturn($remark);
        $log->setRemark($remark);

        $this->assertSame($remark, $log->getRemark());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $log = $this->createMock(AssignLog::class);
        $createdBy = 'admin';

        $log->method('getCreatedBy')->willReturn($createdBy);
        $log->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $log->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $log = $this->createMock(AssignLog::class);
        $updatedBy = 'admin';

        $log->method('getUpdatedBy')->willReturn($updatedBy);
        $log->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $log->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $log = $this->createMock(AssignLog::class);
        $datetime = new \DateTimeImmutable();

        $log->method('getCreateTime')->willReturn($datetime);
        $log->setCreateTime($datetime);

        $this->assertSame($datetime, $log->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $log = $this->createMock(AssignLog::class);
        $datetime = new \DateTimeImmutable();

        $log->method('getUpdateTime')->willReturn($datetime);
        $log->setUpdateTime($datetime);

        $this->assertSame($datetime, $log->getUpdateTime());
    }

    public function testRetrieveAdminArrayReturnsExpectedArray(): void
    {
        $log = $this->createMock(AssignLog::class);

        $expectedArray = [
            'newLevelInfo' => ['id' => 1, 'level' => 2, 'title' => 'VIP2'],
            'oldLevelInfo' => ['id' => 2, 'level' => 1, 'title' => 'VIP1'],
            'userInfo' => ['id' => '123', 'nickName' => '测试用户', 'username' => 'testuser'],
            'assignTime' => '2023-01-01 10:00:00',
            'createTime' => '2023-01-01 10:00:01',
        ];

        $log->method('retrieveAdminArray')->willReturn($expectedArray);
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
