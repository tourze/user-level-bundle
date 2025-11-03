<?php

namespace UserLevelBundle\Tests\Procedure;

use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\GetLevelLogsByBizUserId;

/**
 * @internal
 */
#[CoversClass(GetLevelLogsByBizUserId::class)]
#[RunTestsInSeparateProcesses]
final class GetLevelLogsByBizUserIdTest extends AbstractProcedureTestCase
{
    private GetLevelLogsByBizUserId $procedure;

    protected function onSetUp(): void
    {
        $this->procedure = self::getService(GetLevelLogsByBizUserId::class);
        $this->procedure->paginator = self::getService(PaginatorInterface::class);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetLevelLogsByBizUserId::class, $this->procedure);
    }

    public function testUserIdPropertyCanBeSet(): void
    {
        $this->procedure->userId = 'user123';
        $this->assertEquals('user123', $this->procedure->userId);
    }

    public function testExecute(): void
    {
        $this->procedure->userId = 'test-user-id';
        $result = $this->procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testExecuteWithEmptyUserId(): void
    {
        $this->procedure->userId = '';
        $result = $this->procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertArrayHasKey('pagination', $result);
    }

    public function testFormatItem(): void
    {
        $level1 = new Level();
        $level1->setTitle('Level 1');
        $level1->setLevel(100);
        $level1->setValid(true);

        $level2 = new Level();
        $level2->setTitle('Level 2');
        $level2->setLevel(200);
        $level2->setValid(true);

        $this->persistAndFlush($level1);
        $this->persistAndFlush($level2);

        // 创建一个简单的测试用户对象
        $user = $this->createNormalUser('test-user-' . uniqid(), 'password123');

        $log = new AssignLog();
        $log->setNewLevel($level1);
        $log->setOldLevel($level2);
        $log->setUser($user);
        $log->setType(1);
        $log->setAssignTime(new \DateTimeImmutable());
        $log->setRemark('升级测试');
        $log->setCreatedBy('system');
        $log->setUpdatedBy('system');
        $log->setCreateTime(new \DateTimeImmutable());
        $log->setUpdateTime(new \DateTimeImmutable());

        $this->persistAndFlush($log);

        $reflection = new \ReflectionClass($this->procedure);
        $method = $reflection->getMethod('formatItem');
        $method->setAccessible(true);

        $result = $method->invoke($this->procedure, $log);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('newLevelInfo', $result);
        $this->assertArrayHasKey('oldLevelInfo', $result);
        $this->assertArrayHasKey('assignTime', $result);
    }
}
