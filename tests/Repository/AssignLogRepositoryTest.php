<?php

namespace UserLevelBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Repository\AssignLogRepository;

/**
 * @internal
 */
#[CoversClass(AssignLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class AssignLogRepositoryTest extends AbstractRepositoryTestCase
{
    private AssignLogRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AssignLogRepository::class);
    }

    // 重要：避免跨进程序列化时携带 EntityManager/UnitOfWork
    // Repository 持有 EntityManager 引用，若不置空，PHPUnit 在子进程结束后
    // 会序列化整个对象图（包含实体对象和临时生成的用户实体类），
    // 父进程无法自动加载该临时类，导致 __PHP_Incomplete_Class 赋值到
    // AssignLog::$user（UserInterface 类型）时报错。
    protected function onTearDown(): void
    {
        // 显式释放仓库引用，避免被序列化
        unset($this->repository);
        // 清理 EntityManager 以进一步降低对象图被意外持有的风险
        self::getEntityManager()->clear();
    }

    public function testRepositoryServiceInstantiation(): void
    {
        $this->assertInstanceOf(AssignLogRepository::class, $this->repository);
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testSaveMethod(): void
    {
        $assignLog = $this->createValidAssignLog();

        $this->repository->save($assignLog);

        $this->assertEntityPersisted($assignLog);
        $this->assertNotNull($assignLog->getId());
    }

    public function testSaveWithoutFlush(): void
    {
        $assignLog = $this->createValidAssignLog();

        $this->repository->save($assignLog, flush: false);

        // 在 flush 前验证实体已持久化但未提交到数据库
        $em = self::getEntityManager();
        $this->assertTrue($em->contains($assignLog));

        $em->flush();

        $this->assertEntityPersisted($assignLog);
        $this->assertNotNull($assignLog->getId());
    }

    public function testRemoveMethod(): void
    {
        $assignLog = $this->createValidAssignLog();
        $assignLogId = $assignLog->getId();

        // 验证实体在删除前存在
        $this->assertNotNull($assignLogId);
        $this->assertEntityPersisted($assignLog);

        // 重新获取实体以避免 detached 状态
        $managedAssignLog = $this->repository->find($assignLogId);
        $this->assertNotNull($managedAssignLog);

        $this->repository->remove($managedAssignLog);

        $this->assertEntityNotExists(AssignLog::class, $assignLogId);
    }

    public function testFindWithNonExistentIdShouldReturnNullForAssignLog(): void
    {
        $nonExistentId = '999999999999999999';

        $foundAssignLog = $this->repository->find($nonExistentId);

        $this->assertNull($foundAssignLog);
    }

    public function testFindByWithNonMatchingCriteriaShouldReturnEmptyArrayForAssignLog(): void
    {
        // 使用一个不可能存在的值进行查询
        $results = $this->repository->findBy(['type' => 999]);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindOneByWithNonMatchingCriteriaShouldReturnNullForAssignLog(): void
    {
        $assignLog = $this->createValidAssignLog(['remark' => 'Existing log']);
        $this->repository->save($assignLog);

        $foundAssignLog = $this->repository->findOneBy(['remark' => 'Non-existent remark']);

        $this->assertNull($foundAssignLog);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $assignLog1 = $this->createValidAssignLog(['type' => 1, 'remark' => 'B log']);
        $assignLog2 = $this->createValidAssignLog(['type' => 1, 'remark' => 'A log']);
        $this->repository->save($assignLog1);
        $this->repository->save($assignLog2);

        $foundAssignLog = $this->repository->findOneBy(['type' => 1], ['remark' => 'ASC']);

        $this->assertNotNull($foundAssignLog);
        $this->assertEquals('A log', $foundAssignLog->getRemark());
    }

    public function testBulkOperations(): void
    {
        // 获取初始记录数
        $initialCount = count($this->repository->findAll());

        $assignLogs = [];
        for ($i = 1; $i <= 10; ++$i) {
            $assignLogs[] = $this->createValidAssignLog(['remark' => "Bulk log {$i}"], persist: false);
        }

        // 批量保存
        $em = self::getEntityManager();
        foreach ($assignLogs as $assignLog) {
            $em->persist($assignLog);
        }
        $em->flush();

        $allAssignLogs = $this->repository->findAll();
        // 验证总数增加了 10 条
        $this->assertCount($initialCount + 10, $allAssignLogs);

        // 批量删除
        foreach ($assignLogs as $assignLog) {
            $this->repository->remove($assignLog, flush: false);
        }
        $em->flush();

        $remainingAssignLogs = $this->repository->findAll();
        // 验证只剩下初始的记录
        $this->assertCount($initialCount, $remainingAssignLogs);
    }

    public function testEntityManagerFlushConstraints(): void
    {
        // 测试 flush 和 clear 操作的基本行为
        $assignLog = $this->createValidAssignLog(['remark' => 'Flush test']);

        // 验证记录已保存
        $found = $this->repository->find($assignLog->getId());
        $this->assertNotNull($found);

        // 清除 EntityManager 缓存后重新查找
        $em = self::getEntityManager();
        $em->clear();

        $foundAfterClear = $this->repository->find($assignLog->getId());
        $this->assertNotNull($foundAfterClear);
        $this->assertEquals('Flush test', $foundAfterClear->getRemark());
    }

    public function testCountByAssociationNewLevelShouldReturnCorrectNumber(): void
    {
        $level1 = $this->createLevel(100, 'Bronze Level');
        $level2 = $this->createLevel(200, 'Silver Level');

        $this->createValidAssignLog(['newLevel' => $level1]);
        $this->createValidAssignLog(['newLevel' => $level2]);
        $this->createValidAssignLog(['newLevel' => $level1]);

        $bronzeCount = $this->repository->count(['newLevel' => $level1]);
        $silverCount = $this->repository->count(['newLevel' => $level2]);

        $this->assertEquals(2, $bronzeCount);
        $this->assertEquals(1, $silverCount);
    }

    public function testCountByAssociationOldLevelShouldReturnCorrectNumber(): void
    {
        $level1 = $this->createLevel(50, 'Starter Level');
        $level2 = $this->createLevel(100, 'Bronze Level');

        $this->createValidAssignLog(['oldLevel' => $level1]);
        $this->createValidAssignLog(['oldLevel' => $level2]);
        $this->createValidAssignLog(['oldLevel' => $level1]);

        $starterCount = $this->repository->count(['oldLevel' => $level1]);
        $bronzeCount = $this->repository->count(['oldLevel' => $level2]);

        $this->assertEquals(2, $starterCount);
        $this->assertEquals(1, $bronzeCount);
    }

    public function testCountByAssociationUserShouldReturnCorrectNumber(): void
    {
        $user1 = $this->createNormalUser('user1-' . uniqid(), 'password123');
        $user2 = $this->createNormalUser('user2-' . uniqid(), 'password123');

        $this->createValidAssignLog(['user' => $user1]);
        $this->createValidAssignLog(['user' => $user2]);
        $this->createValidAssignLog(['user' => $user1]);

        $user1Count = $this->repository->count(['user' => $user1]);
        $user2Count = $this->repository->count(['user' => $user2]);

        $this->assertEquals(2, $user1Count);
        $this->assertEquals(1, $user2Count);
    }

    public function testFindOneByAssociationNewLevelShouldReturnMatchingEntity(): void
    {
        $level1 = $this->createLevel(100, 'Bronze Level');
        $level2 = $this->createLevel(200, 'Silver Level');

        $assignLog1 = $this->createValidAssignLog(['newLevel' => $level1, 'remark' => 'Bronze log']);
        $assignLog2 = $this->createValidAssignLog(['newLevel' => $level2, 'remark' => 'Silver log']);

        $foundAssignLog = $this->repository->findOneBy(['newLevel' => $level1]);

        $this->assertNotNull($foundAssignLog);
        $this->assertEquals($assignLog1->getId(), $foundAssignLog->getId());
        $this->assertEquals('Bronze log', $foundAssignLog->getRemark());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createValidAssignLog(array $overrides = [], bool $persist = true): AssignLog
    {
        $assignLog = new AssignLog();

        // 设置必需的关联实体
        $assignLog->setOldLevel($this->extractLevelOverride($overrides, 'oldLevel'));
        $assignLog->setNewLevel($this->extractLevelOverride($overrides, 'newLevel'));
        $assignLog->setUser($this->extractUserOverride($overrides));

        // 设置其他属性
        $assignLog->setType($this->extractIntOverride($overrides, 'type', 1));
        $assignLog->setRemark($this->extractStringOverride($overrides, 'remark', 'Test assignment log'));
        $assignLog->setAssignTime($this->extractDateTimeOverride($overrides, 'assignTime'));

        if ($persist) {
            $this->persistAndFlush($assignLog);
        }

        return $assignLog;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function extractLevelOverride(array $overrides, string $key): Level
    {
        if (isset($overrides[$key]) && $overrides[$key] instanceof Level) {
            return $overrides[$key];
        }

        return $this->createLevel();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function extractUserOverride(array $overrides): UserInterface
    {
        if (isset($overrides['user']) && $overrides['user'] instanceof UserInterface) {
            return $overrides['user'];
        }

        return $this->createNormalUser('testuser' . time() . mt_rand(1000, 9999), 'password123');
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function extractIntOverride(array $overrides, string $key, int $default): int
    {
        if (isset($overrides[$key]) && is_int($overrides[$key])) {
            return $overrides[$key];
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function extractStringOverride(array $overrides, string $key, string $default): string
    {
        if (isset($overrides[$key]) && is_string($overrides[$key])) {
            return $overrides[$key];
        }

        return $default;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function extractDateTimeOverride(array $overrides, string $key): \DateTimeInterface
    {
        if (isset($overrides[$key]) && $overrides[$key] instanceof \DateTimeInterface) {
            return $overrides[$key];
        }

        return new \DateTimeImmutable();
    }

    private function createLevel(?int $level = null, ?string $title = null): Level
    {
        if (null === $level) {
            // 使用当前时间戳+随机数来生成唯一的 level 值
            $level = time() + mt_rand(1, 999999);
        }
        if (null === $title) {
            $title = "Level {$level}";
        }

        $levelEntity = new Level();
        $levelEntity->setLevel($level);
        $levelEntity->setTitle($title);
        $levelEntity->setValid(true);

        $this->persistAndFlush($levelEntity);

        return $levelEntity;
    }

    protected function createNewEntity(): object
    {
        $entity = new AssignLog();

        // 设置必需的关联实体
        $entity->setOldLevel($this->createLevel());
        $entity->setNewLevel($this->createLevel());
        $entity->setUser($this->createNormalUser('test-entity-' . uniqid(), 'password123'));

        // 设置基本字段
        $entity->setType(1);
        $entity->setAssignTime(new \DateTimeImmutable());
        $entity->setRemark('Test remark');
        $entity->setCreatedBy('system');
        $entity->setUpdatedBy('system');
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setUpdateTime(new \DateTimeImmutable());

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<AssignLog>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
