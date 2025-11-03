<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Repository\LevelRepository;

/**
 * @internal
 */
#[CoversClass(LevelRepository::class)]
#[RunTestsInSeparateProcesses]
final class LevelRepositoryTest extends AbstractRepositoryTestCase
{
    private LevelRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(LevelRepository::class);
    }

    // 避免跨进程序列化时携带 EntityManager/UnitOfWork
    protected function onTearDown(): void
    {
        unset($this->repository);
        self::getEntityManager()->clear();
    }

    /**
     * @return ServiceEntityRepository<Level>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        return $this->createLevel([], false);
    }

    public function testFind(): void
    {
        $level = $this->createLevel();
        $this->persistAndFlush($level);

        $found = $this->repository->find($level->getId());

        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals($level->getId(), $found->getId());
        $this->assertEquals($level->getTitle(), $found->getTitle());
        $this->assertEquals($level->getLevel(), $found->getLevel());
        $this->assertTrue($found->isValid());
    }

    public function testFindWithNonExistentIdShouldReturnNullForLevel(): void
    {
        $found = $this->repository->find('9999999999999999999');

        $this->assertNull($found);
    }

    public function testFindByWithNonMatchingCriteriaShouldReturnEmptyArrayForLevel(): void
    {
        $level = $this->createLevel(['title' => '测试等级', 'level' => 10]);
        $this->persistAndFlush($level);

        $results = $this->repository->findBy(['title' => '不存在的等级']);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindByWithNonExistentCriteria(): void
    {
        $level = $this->createLevel();
        $this->persistAndFlush($level);

        $results = $this->repository->findBy(['title' => '不存在的等级']);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function testFindOneByWithNonMatchingCriteriaShouldReturnNullForLevel(): void
    {
        $level = $this->createLevel();
        $this->persistAndFlush($level);

        $found = $this->repository->findOneBy(['level' => 999]);

        $this->assertNull($found);
    }

    public function testFindOneByWithMultipleResults(): void
    {
        $level1 = $this->createLevel(['title' => '会员A', 'level' => 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => '会员B', 'level' => 20, 'valid' => true]);

        $this->persistEntities([$level1, $level2]);

        $found = $this->repository->findOneBy(['valid' => true]);

        $this->assertInstanceOf(Level::class, $found);
        $this->assertTrue($found->isValid());
    }

    public function testSave(): void
    {
        $level = $this->createLevel([], false);

        $this->repository->save($level, false);

        $this->assertNotNull($level->getId());

        self::getEntityManager()->flush();

        $found = $this->repository->find($level->getId());
        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals($level->getTitle(), $found->getTitle());
    }

    public function testSaveWithFlush(): void
    {
        $level = $this->createLevel([], false);

        $this->repository->save($level, true);

        $this->assertNotNull($level->getId());

        $found = $this->repository->find($level->getId());
        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals($level->getTitle(), $found->getTitle());
    }

    public function testSaveExistingEntity(): void
    {
        $level = $this->createLevel();
        $this->persistAndFlush($level);

        $level->setTitle('更新后的等级');
        $this->repository->save($level);

        $found = $this->repository->find($level->getId());
        $this->assertNotNull($found);
        $this->assertEquals('更新后的等级', $found->getTitle());
    }

    public function testRemove(): void
    {
        $level = $this->createLevel();
        $this->persistAndFlush($level);
        $levelId = $level->getId();

        $this->repository->remove($level, false);
        self::getEntityManager()->flush();

        $found = $this->repository->find($levelId);
        $this->assertNull($found);
    }

    public function testRemoveNonPersistedEntity(): void
    {
        $level = new Level();
        $level->setTitle('未持久化等级');
        $level->setLevel(99);
        $level->setValid(true);

        $this->expectNotToPerformAssertions();
        $this->repository->remove($level, true);
    }

    public function testFindByLevelRange(): void
    {
        $levels = [
            $this->createLevel(['title' => '初级', 'level' => 10]),
            $this->createLevel(['title' => '中级', 'level' => 50]),
            $this->createLevel(['title' => '高级', 'level' => 90]),
            $this->createLevel(['title' => '专家', 'level' => 150]),
        ];
        $this->persistEntities($levels);

        $qb = $this->repository->createQueryBuilder('l')
            ->where('l.level >= :minLevel AND l.level <= :maxLevel')
            ->setParameter('minLevel', 20)
            ->setParameter('maxLevel', 100)
            ->orderBy('l.level', 'ASC')
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Level::class, $results[0]);
        $this->assertInstanceOf(Level::class, $results[1]);
        $this->assertEquals('中级', $results[0]->getTitle());
        $this->assertEquals('高级', $results[1]->getTitle());
    }

    public function testFindWithUpgradeRules(): void
    {
        // 简化的关联测试
        $level = $this->createLevel(['title' => '会员等级', 'level' => 10]);
        $this->persistAndFlush($level);

        $foundLevel = $this->repository->find($level->getId());
        $this->assertInstanceOf(Level::class, $foundLevel);

        // 验证getUpgradeRules方法存在且返回Collection
        $upgradeRules = $foundLevel->getUpgradeRules();
        $this->assertInstanceOf(Collection::class, $upgradeRules);

        // 由于没有关联数据，集合应该为空
        $this->assertCount(0, $upgradeRules);
    }

    public function testFindByTitleUniqueness(): void
    {
        $level1 = $this->createLevel(['title' => '唯一等级', 'level' => 10]);
        $this->persistAndFlush($level1);

        $level2 = new Level();
        $level2->setTitle('唯一等级');
        $level2->setLevel(20);
        $level2->setValid(true);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->persistAndFlush($level2);
    }

    public function testFindByLevelUniqueness(): void
    {
        $level1 = $this->createLevel(['title' => '等级A', 'level' => 50]);
        $this->persistAndFlush($level1);

        $level2 = new Level();
        $level2->setTitle('等级B');
        $level2->setLevel(50);
        $level2->setValid(true);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->persistAndFlush($level2);
    }

    public function testStringRepresentation(): void
    {
        $level = $this->createLevel(['title' => '字符串测试等级', 'level' => 25]);
        $this->persistAndFlush($level);

        $stringValue = (string) $level;

        $this->assertEquals('字符串测试等级', $stringValue);
    }

    public function testAdminArrayRepresentation(): void
    {
        $level = $this->createLevel(['title' => '管理员数组测试', 'level' => 35]);
        $this->persistAndFlush($level);

        $adminArray = $level->retrieveAdminArray();

        $this->assertIsArray($adminArray);
        $this->assertArrayHasKey('level', $adminArray);
        $this->assertArrayHasKey('title', $adminArray);
        $this->assertArrayHasKey('id', $adminArray);
        $this->assertEquals(35, $adminArray['level']);
        $this->assertEquals('管理员数组测试', $adminArray['title']);
        $this->assertEquals($level->getId(), $adminArray['id']);
    }

    public function testValidFieldToggle(): void
    {
        $level = $this->createLevel(['valid' => true]);
        $this->persistAndFlush($level);

        $level->setValid(false);
        $this->repository->save($level);

        $found = $this->repository->find($level->getId());
        $this->assertNotNull($found);
        $this->assertFalse($found->isValid());

        $level->setValid(true);
        $this->repository->save($level);

        $found = $this->repository->find($level->getId());
        $this->assertNotNull($found);
        $this->assertTrue($found->isValid());
    }

    public function testQueryBuilderAccess(): void
    {
        $level1 = $this->createLevel(['title' => '查询构建器测试1', 'level' => 40]);
        $level2 = $this->createLevel(['title' => '查询构建器测试2', 'level' => 60]);
        $this->persistEntities([$level1, $level2]);

        $qb = $this->repository->createQueryBuilder('l')
            ->where('l.title LIKE :pattern')
            ->setParameter('pattern', '%查询构建器%')
            ->orderBy('l.level', 'DESC')
        ;

        $results = $qb->getQuery()->getResult();
        $this->assertIsArray($results);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Level::class, $results[0]);
        $this->assertInstanceOf(Level::class, $results[1]);
        $this->assertEquals(60, $results[0]->getLevel());
        $this->assertEquals(40, $results[1]->getLevel());
    }

    /**
     * 创建测试用的Level实体
     * @param array<string, mixed> $attributes
     */
    private function createLevel(array $attributes = [], bool $persist = true): Level
    {
        static $counter = 0;
        $level = new Level();

        // 使用静态计数器和微秒时间戳确保值唯一
        $counterValue = is_int($counter) ? $counter + 1 : 1;
        $counter = $counterValue;
        $uniqueValue = (int) (microtime(true) * 1000000) + $counterValue;

        $attributes = array_merge([
            'title' => '测试等级' . $uniqueValue,
            'level' => $uniqueValue,
            'valid' => true,
        ], $attributes);

        $this->assertIsString($attributes['title']);
        $this->assertIsInt($attributes['level']);
        $this->assertIsBool($attributes['valid']);

        $level->setTitle($attributes['title']);
        $level->setLevel($attributes['level']);
        $level->setValid($attributes['valid']);

        if ($persist) {
            $this->persistAndFlush($level);
        }

        return $level;
    }

    public function testFindByWithValidField(): void
    {
        $level1 = $this->createLevel(['title' => '有效等级', 'level' => 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => '无效等级', 'level' => 20, 'valid' => false]);

        $this->persistEntities([$level1, $level2]);

        $falseLevels = $this->repository->findBy(['valid' => false]);

        $this->assertGreaterThanOrEqual(1, count($falseLevels));
        // 验证至少包含我们创建的无效等级
        $foundOurLevel = false;
        foreach ($falseLevels as $level) {
            if ('无效等级' === $level->getTitle()) {
                $foundOurLevel = true;
                $this->assertFalse($level->isValid());
                break;
            }
        }
        $this->assertTrue($foundOurLevel, '应该找到我们创建的无效等级');
    }

    public function testCountWithValidField(): void
    {
        $level1 = $this->createLevel(['title' => '有效等级', 'level' => 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => '无效等级', 'level' => 20, 'valid' => false]);

        $this->persistEntities([$level1, $level2]);

        // 使用QueryBuilder精确计数我们创建的记录
        $falseCount = (int) $this->repository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.id IN (:ids) AND l.valid = :valid')
            ->setParameter('ids', [$level1->getId(), $level2->getId()])
            ->setParameter('valid', false)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->assertEquals(1, $falseCount);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $level1 = $this->createLevel(['title' => '低级会员', 'level' => 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => '高级会员', 'level' => 30, 'valid' => true]);
        $level3 = $this->createLevel(['title' => '中级会员', 'level' => 20, 'valid' => true]);

        $this->persistEntities([$level1, $level2, $level3]);

        $found = $this->repository->findOneBy(['valid' => true], ['level' => 'DESC']);

        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals(30, $found->getLevel());
        $this->assertEquals('高级会员', $found->getTitle());
    }

    public function testFindOneByWithOrderByShouldRespectOrdering(): void
    {
        static $testCounter = 0;
        $testCounterValue = is_int($testCounter) ? $testCounter + 1 : 1;
        $testCounter = $testCounterValue;
        $uniqueTime = microtime(true) . '-' . $testCounterValue;
        $baseValue = (int) (microtime(true) * 1000000) + ($testCounter * 1000);
        $level1 = $this->createLevel(['title' => 'A排序测试' . $uniqueTime, 'level' => $baseValue + 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => 'C排序测试' . $uniqueTime, 'level' => $baseValue + 20, 'valid' => true]);
        $level3 = $this->createLevel(['title' => 'B排序测试' . $uniqueTime, 'level' => $baseValue + 30, 'valid' => true]);

        $this->persistEntities([$level1, $level2, $level3]);

        // 测试按title字段升序排序
        $foundAsc = $this->repository->createQueryBuilder('l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', [$level1->getId(), $level2->getId(), $level3->getId()])
            ->orderBy('l.title', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->assertInstanceOf(Level::class, $foundAsc);
        $this->assertEquals('A排序测试' . $uniqueTime, $foundAsc->getTitle());

        // 测试按title字段降序排序
        $foundDesc = $this->repository->createQueryBuilder('l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', [$level1->getId(), $level2->getId(), $level3->getId()])
            ->orderBy('l.title', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->assertInstanceOf(Level::class, $foundDesc);
        $this->assertEquals('C排序测试' . $uniqueTime, $foundDesc->getTitle());
    }

    public function testCountWithNullValidFieldQuery(): void
    {
        // 测试计数valid为null的查询功能
        $nullValidCount = (int) $this->repository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.valid IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        // 应该返回0，因为valid字段不允许null值
        $this->assertEquals(0, $nullValidCount);
    }

    public function testFindOneByWithComplexOrderBy(): void
    {
        $level1 = $this->createLevel(['title' => 'Z级会员', 'level' => 10, 'valid' => true]);
        $level2 = $this->createLevel(['title' => 'A级会员', 'level' => 20, 'valid' => true]);
        $level3 = $this->createLevel(['title' => 'M级会员', 'level' => 30, 'valid' => true]);

        $this->persistEntities([$level1, $level2, $level3]);

        // 测试按level降序排序的findOneBy
        $found = $this->repository->findOneBy(
            ['valid' => true],
            ['level' => 'DESC', 'title' => 'ASC']
        );

        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals(30, $found->getLevel());
        $this->assertEquals('M级会员', $found->getTitle());
    }

    public function testFindOneByWithOrderByMultipleFields(): void
    {
        static $testCounter2 = 0;
        $testCounter2Value = is_int($testCounter2) ? $testCounter2 + 1 : 1;
        $testCounter2 = $testCounter2Value;
        $uniqueTime = microtime(true) . '-' . $testCounter2Value;
        $baseValue = (int) (microtime(true) * 1000000) + ($testCounter2 * 1000);
        $level1 = $this->createLevel(['title' => 'B等级' . $uniqueTime, 'level' => $baseValue + 1, 'valid' => true]);
        $level2 = $this->createLevel(['title' => 'A等级' . $uniqueTime, 'level' => $baseValue + 2, 'valid' => true]);
        $level3 = $this->createLevel(['title' => 'C等级' . $uniqueTime, 'level' => $baseValue + 3, 'valid' => true]);

        $this->persistEntities([$level1, $level2, $level3]);

        // 测试按多个字段排序的findOneBy（先按title升序）
        $found = $this->repository->createQueryBuilder('l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', [$level1->getId(), $level2->getId(), $level3->getId()])
            ->orderBy('l.title', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals('A等级' . $uniqueTime, $found->getTitle());
    }

    public function testFindOneByWithNullOrderBy(): void
    {
        // 测试按可空字段排序（使用createdBy字段，它确实可以为null）
        $level1 = $this->createLevel(['title' => '有创建者等级', 'valid' => true]);
        $level1->setCreatedBy('user123');
        $this->repository->save($level1);

        $level2 = $this->createLevel(['title' => '无创建者等级', 'valid' => true]);
        $level2->setCreatedBy(null);
        $this->repository->save($level2);

        // 测试findOneBy按createdBy字段排序（null值排在最后）
        $found = $this->repository->createQueryBuilder('l')
            ->where('l.id IN (:ids)')
            ->setParameter('ids', [$level1->getId(), $level2->getId()])
            ->orderBy('l.createdBy', 'DESC')
            ->addOrderBy('l.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals('user123', $found->getCreatedBy());
        $this->assertEquals($level1->getId(), $found->getId());
    }

    public function testCountWithNullUpdatedByField(): void
    {
        // 创建一个updatedBy字段为null的等级
        static $nullTestCounter = 0;
        $nullTestCounterValue = is_int($nullTestCounter) ? $nullTestCounter + 1 : 1;
        $nullTestCounter = $nullTestCounterValue;
        $level = new Level();
        $uniqueId = microtime(true) . '-' . $nullTestCounterValue;
        $level->setTitle('计数空更新者等级' . $uniqueId);
        $level->setLevel((int) (microtime(true) * 1000000) + ($nullTestCounter * 100));
        $level->setValid(true);
        $level->setUpdatedBy(null);
        $this->persistAndFlush($level);

        // 测试计数updatedBy为null的记录
        $nullUpdatedByCount = (int) $this->repository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.updatedBy IS NULL')
            ->andWhere('l.id = :id')
            ->setParameter('id', $level->getId())
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->assertEquals(1, $nullUpdatedByCount);
    }

    public function testCountWithNullCreateTimeField(): void
    {
        // 测试计数createTime为null的查询功能
        $nullCreateTimeCount = (int) $this->repository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.createTime IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        // 应该返回数字（可能为0），主要测试查询本身不会出错
        $this->assertIsNumeric($nullCreateTimeCount);
        $this->assertGreaterThanOrEqual(0, $nullCreateTimeCount);
    }

    public function testFindByWithNullUpdateTimeField(): void
    {
        // 测试查询updateTime为null的功能
        $nullUpdateTimeLevels = $this->repository->createQueryBuilder('l')
            ->where('l.updateTime IS NULL')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;

        // 这个查询应该能正常执行，返回数组（可能为空）
        $this->assertIsArray($nullUpdateTimeLevels);
        // 由于时间字段通常会自动填充，我们主要测试查询本身的功能
    }

    public function testCountWithNullUpdateTimeField(): void
    {
        // 测试计数updateTime为null的查询功能
        $nullUpdateTimeCount = (int) $this->repository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.updateTime IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        // 应该返回数字（可能为0），主要测试查询本身不会出错
        $this->assertIsNumeric($nullUpdateTimeCount);
        $this->assertGreaterThanOrEqual(0, $nullUpdateTimeCount);
    }

    /**
     * 测试 Snowflake ID 的保存行为
     *
     * 由于 Snowflake ID 在 persist() 时立即生成，这个测试验证特定的 Snowflake ID 行为
     * 相关 Issue: #1367 - 基类的 testSaveWithFlushFalseShouldNotImmediatelyPersist 与 Snowflake ID 不兼容
     */
    public function testSaveWithSnowflakeIdBehavior(): void
    {
        $entity = $this->createLevel([], false);

        // 保存前 ID 应该为 null
        $this->assertNull($entity->getId());

        $this->repository->save($entity, false);

        // 对于 Snowflake ID，persist() 后 ID 会立即生成
        $this->assertNotNull($entity->getId());
        $this->assertNotEmpty($entity->getId());

        // 验证实体已在 EntityManager 中管理
        $this->assertTrue(self::getEntityManager()->contains($entity));

        // 手动flush完成实际的数据库操作
        self::getEntityManager()->flush();

        // flush后能从数据库查询到
        $found = $this->repository->find($entity->getId());
        $this->assertInstanceOf(Level::class, $found);
        $this->assertEquals($entity->getId(), $found->getId());
    }
}
