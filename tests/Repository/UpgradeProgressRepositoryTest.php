<?php

namespace UserLevelBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;
use UserLevelBundle\Repository\UpgradeProgressRepository;

/**
 * @internal
 */
#[CoversClass(UpgradeProgressRepository::class)]
#[RunTestsInSeparateProcesses]
final class UpgradeProgressRepositoryTest extends AbstractRepositoryTestCase
{
    private UpgradeProgressRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(UpgradeProgressRepository::class);
    }

    // 重要：避免跨进程序列化时携带 EntityManager/UnitOfWork
    // Repository 持有 EntityManager 引用，若不置空，PHPUnit 在子进程结束后
    // 会序列化整个对象图（包含实体对象和临时生成的用户实体类），
    // 父进程无法自动加载该临时类，导致 __PHP_Incomplete_Class 赋值到
    // UpgradeProgress::$user（UserInterface 类型）时报错。
    protected function onTearDown(): void
    {
        // 显式释放仓库引用，避免被序列化
        unset($this->repository);
        // 清理 EntityManager 以进一步降低对象图被意外持有的风险
        self::getEntityManager()->clear();
    }

    /**
     * @return ServiceEntityRepository<UpgradeProgress>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        static $counter = 0;
        $counterValue = is_int($counter) ? $counter + 1 : 1;
        $counter = $counterValue;

        $user = $this->createNormalUser('test' . $counter . '@example.com', 'password123');
        $level = $this->createLevel('Test', 1);
        $upgradeRule = $this->createUpgradeRule('Test Rule', 100, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(50);

        return $progress;
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(UpgradeProgressRepository::class, $this->repository);
    }

    public function testFindWithNonExistentIdShouldReturnNullForUpgradeProgress(): void
    {
        $found = $this->repository->find(999999);
        $this->assertNull($found);
    }

    public function testFindByWithNonMatchingCriteriaShouldReturnEmptyArrayForUpgradeProgress(): void
    {
        $results = $this->repository->findBy(['value' => 999999]);
        $this->assertIsArray($results);
        $this->assertCount(0, $results);
    }

    public function testFindOneByWithNonMatchingCriteriaShouldReturnNullForUpgradeProgress(): void
    {
        $found = $this->repository->findOneBy(['value' => 888888]);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderingShouldRespectOrder(): void
    {
        // 使用唯一的level和rule确保测试隔离
        $level = $this->createLevel('TestSortLevel', 999);
        $upgradeRule = $this->createUpgradeRule('TestSortRule', 2000, $level);

        // 创建两个进度记录，确保有明显差异
        $user1 = $this->createNormalUser('sort_test1@example.com', 'password123');
        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(50);  // 明确的最小值
        $this->repository->save($progress1, true);  // 强制flush

        $user2 = $this->createNormalUser('sort_test2@example.com', 'password123');
        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(950);  // 明确的最大值
        $this->repository->save($progress2, true);  // 强制flush

        // 测试排序功能 - 验证查询可以执行并返回结果
        $ascResult = $this->repository->findOneBy(['upgradeRule' => $upgradeRule], ['value' => 'ASC']);
        $this->assertNotNull($ascResult);
        $this->assertInstanceOf(UpgradeProgress::class, $ascResult);

        $descResult = $this->repository->findOneBy(['upgradeRule' => $upgradeRule], ['value' => 'DESC']);
        $this->assertNotNull($descResult);
        $this->assertInstanceOf(UpgradeProgress::class, $descResult);

        // 验证返回的是我们创建的记录之一
        $this->assertContains($ascResult->getValue(), [50, 950]);
        $this->assertContains($descResult->getValue(), [50, 950]);
    }

    public function testQueryWithUserAssociation(): void
    {
        $user1 = $this->createNormalUser('association_user1@example.com', 'password123');
        $user2 = $this->createNormalUser('association_user2@example.com', 'password123');
        $level = $this->createLevel('AssociationTestLevel', 10);
        $upgradeRule = $this->createUpgradeRule('AssociationTestRule', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(40);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(70);
        $this->repository->save($progress2, true);  // 强制flush

        $user1Progress = $this->repository->findBy(['user' => $user1]);
        $this->assertCount(1, $user1Progress);
        $this->assertIsArray($user1Progress);
        $this->assertInstanceOf(UpgradeProgress::class, $user1Progress[0]);
        $this->assertEquals(40, $user1Progress[0]->getValue());
    }

    public function testCountWithUserAssociation(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(45);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(99);
        $this->repository->save($progress2, true);  // 强制flush

        $user1Count = $this->repository->count(['user' => $user1]);
        $this->assertEquals(1, $user1Count);

        $user2Count = $this->repository->count(['user' => $user2]);
        $this->assertEquals(1, $user2Count);
    }

    public function testQueryWithUpgradeRuleAssociation(): void
    {
        $user1 = $this->createNormalUser('test1@example.com', 'password123');
        $user2 = $this->createNormalUser('test2@example.com', 'password123');
        $level1 = $this->createLevel('Bronze', 1);
        $level2 = $this->createLevel('Silver', 2);
        $upgradeRule1 = $this->createUpgradeRule('Bronze Rule', 100, $level1);
        $upgradeRule2 = $this->createUpgradeRule('Silver Rule', 500, $level2);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule1);
        $progress1->setValue(80);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule2);
        $progress2->setValue(300);
        $this->repository->save($progress2, true);  // 强制flush

        $bronzeProgress = $this->repository->findBy(['upgradeRule' => $upgradeRule1]);
        $this->assertCount(1, $bronzeProgress);
        $this->assertIsArray($bronzeProgress);
        $this->assertInstanceOf(UpgradeProgress::class, $bronzeProgress[0]);
        $this->assertEquals(80, $bronzeProgress[0]->getValue());

        $silverProgress = $this->repository->findBy(['upgradeRule' => $upgradeRule2]);
        $this->assertCount(1, $silverProgress);
        $this->assertIsArray($silverProgress);
        $this->assertInstanceOf(UpgradeProgress::class, $silverProgress[0]);
        $this->assertEquals(300, $silverProgress[0]->getValue());
    }

    public function testQueryWithValueRange(): void
    {
        // 获取初始的记录数
        $em = self::getEntityManager();
        $initialPositiveQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value > 0'
        );
        $initialPositiveCount = (int) $initialPositiveQuery->getSingleScalarResult();

        $initialZeroQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value = 0'
        );
        $initialZeroCount = (int) $initialZeroQuery->getSingleScalarResult();

        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(50);
        $this->repository->save($progress1, true);  // 强制flush

        $user2 = $this->createNormalUser('test2@example.com', 'password123');
        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(0);
        $this->repository->save($progress2, true);  // 强制flush

        $positiveValues = $this->repository->createQueryBuilder('up')
            ->where('up.value > :zero')
            ->setParameter('zero', 0)
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($positiveValues);
        $this->assertCount($initialPositiveCount + 1, $positiveValues);

        $zeroValues = $this->repository->createQueryBuilder('up')
            ->where('up.value = :zero')
            ->setParameter('zero', 0)
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($zeroValues);
        $this->assertCount($initialZeroCount + 1, $zeroValues);
    }

    public function testCountWithValueRange(): void
    {
        // 获取初始的记录数
        $em = self::getEntityManager();
        $initialLowQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value < 50'
        );
        $initialLowCount = (int) $initialLowQuery->getSingleScalarResult();

        $initialHighQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value >= 50'
        );
        $initialHighCount = (int) $initialHighQuery->getSingleScalarResult();

        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(25);
        $this->repository->save($progress1, true);  // 强制flush

        $user2 = $this->createNormalUser('test2@example.com', 'password123');
        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(75);
        $this->repository->save($progress2, true);  // 强制flush

        $lowValuesCount = (int) $this->repository->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.value < :threshold')
            ->setParameter('threshold', 50)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $this->assertEquals($initialLowCount + 1, $lowValuesCount);

        $highValuesCount = (int) $this->repository->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.value >= :threshold')
            ->setParameter('threshold', 50)
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $this->assertEquals($initialHighCount + 1, $highValuesCount);
    }

    public function testSaveMethodPersistsEntity(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Save Test Rule', 200, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(120);

        $this->repository->save($progress, true);  // 强制flush

        $this->assertNotNull($progress->getId());

        $found = $this->repository->find($progress->getId());
        $this->assertNotNull($found);
        $this->assertEquals(120, $found->getValue());
    }

    public function testSaveMethodWithFlushFalseShouldNotFlush(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('No Flush Rule', 300, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(180);

        $initialCount = $this->repository->count([]);

        $this->repository->save($progress, false);

        $countAfterSave = $this->repository->count([]);
        $this->assertEquals($initialCount, $countAfterSave);

        self::getEntityManager()->flush();

        $countAfterFlush = $this->repository->count([]);
        $this->assertEquals($initialCount + 1, $countAfterFlush);
    }

    public function testRemoveMethodDeletesEntity(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Remove Test Rule', 150, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(75);

        $this->repository->save($progress, true);  // 强制flush
        $id = $progress->getId();

        $this->repository->remove($progress);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testRemoveMethodWithFlushFalseShouldNotFlush(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('No Remove Flush Rule', 400, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(220);

        $this->repository->save($progress, true);  // 强制flush以创建实体
        $id = $progress->getId();

        $this->repository->remove($progress, false);

        $found = $this->repository->find($id);
        $this->assertNotNull($found);

        self::getEntityManager()->flush();
        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testQueryWithJoinOnUserAssociation(): void
    {
        $user = $this->createNormalUser('join@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Join Rule', 600, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(350);
        $this->repository->save($progress, true);  // 强制flush

        $results = $this->repository->createQueryBuilder('up')
            ->where('up.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(UpgradeProgress::class, $results[0]);
        $this->assertEquals(350, $results[0]->getValue());
    }

    public function testQueryWithJoinOnUpgradeRuleAssociation(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Gold', 3);
        $upgradeRule = $this->createUpgradeRule('Complex Join Rule', 1000, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(850);
        $this->repository->save($progress, true);  // 强制flush

        $results = $this->repository->createQueryBuilder('up')
            ->join('up.upgradeRule', 'ur')
            ->where('ur.title LIKE :title')
            ->setParameter('title', 'Complex Join Rule%')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(UpgradeProgress::class, $results[0]);
        $this->assertEquals(850, $results[0]->getValue());
    }

    public function testQueryWithNestedJoinOnLevelAssociation(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Platinum', 4);
        $upgradeRule = $this->createUpgradeRule('Nested Join Rule', 2000, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(1500);
        $this->repository->save($progress, true);  // 强制flush

        $results = $this->repository->createQueryBuilder('up')
            ->join('up.upgradeRule', 'ur')
            ->join('ur.userLevel', 'l')
            ->where('l.title LIKE :levelTitle')
            ->setParameter('levelTitle', 'Platinum%')
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(UpgradeProgress::class, $results[0]);
        $this->assertEquals(1500, $results[0]->getValue());
    }

    public function testQueryWithValueComparison(): void
    {
        // 获取初始的记录数
        $em = self::getEntityManager();
        $initialLowQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value < 50'
        );
        $initialLowCount = (int) $initialLowQuery->getSingleScalarResult();

        $initialHighQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value >= 50'
        );
        $initialHighCount = (int) $initialHighQuery->getSingleScalarResult();

        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Value Rule', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(30);
        $this->repository->save($progress1, true);  // 强制flush

        $user2 = $this->createNormalUser('test2@example.com', 'password123');
        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(80);
        $this->repository->save($progress2, true);  // 强制flush

        $lowProgress = $this->repository->createQueryBuilder('up')
            ->where('up.value < :threshold')
            ->setParameter('threshold', 50)
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($lowProgress);
        $this->assertCount($initialLowCount + 1, $lowProgress);

        // 验证我们创建的记录在结果中
        $foundOurProgress = false;
        foreach ($lowProgress as $progress) {
            $this->assertInstanceOf(UpgradeProgress::class, $progress);
            if (30 === $progress->getValue()) {
                $foundOurProgress = true;
                break;
            }
        }
        $this->assertTrue($foundOurProgress, 'Our created progress with value 30 should be in the results');

        $highProgress = $this->repository->createQueryBuilder('up')
            ->where('up.value >= :threshold')
            ->setParameter('threshold', 50)
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($highProgress);
        $this->assertCount($initialHighCount + 1, $highProgress);

        // 验证我们创建的记录在结果中
        $foundOurHighProgress = false;
        foreach ($highProgress as $progress) {
            $this->assertInstanceOf(UpgradeProgress::class, $progress);
            if (80 === $progress->getValue()) {
                $foundOurHighProgress = true;
                break;
            }
        }
        $this->assertTrue($foundOurHighProgress, 'Our created progress with value 80 should be in the results');
    }

    public function testQueryWithValueZero(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Zero Rule', 100, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(0);
        $this->repository->save($progress, true);  // 强制flush

        $zeroResults = $this->repository->createQueryBuilder('up')
            ->where('up.value = :zero')
            ->setParameter('zero', 0)
            ->getQuery()
            ->getResult()
        ;

        $this->assertIsArray($zeroResults);
        $this->assertCount(1, $zeroResults);
        $this->assertInstanceOf(UpgradeProgress::class, $zeroResults[0]);
        $this->assertEquals(0, $zeroResults[0]->getValue());
    }

    public function testCountWithValueZero(): void
    {
        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Zero Count Rule', 100, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(0);
        $this->repository->save($progress, true);  // 强制flush

        $count = (int) $this->repository->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.value = :zero')
            ->setParameter('zero', 0)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        $this->assertEquals(1, $count);
    }

    public function testQueryWithNullValueQuery(): void
    {
        // 获取初始的记录数
        $em = self::getEntityManager();
        $initialNotNullQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value IS NOT NULL'
        );
        $initialNotNullCount = (int) $initialNotNullQuery->getSingleScalarResult();

        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progressWithValue = new UpgradeProgress();
        $progressWithValue->setUser($user);
        $progressWithValue->setUpgradeRule($upgradeRule);
        $progressWithValue->setValue(50);
        $this->repository->save($progressWithValue, true);  // 强制flush

        $nullResults = $this->repository->createQueryBuilder('up')
            ->where('up.value IS NULL')
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($nullResults);
        $this->assertCount(0, $nullResults);

        $notNullResults = $this->repository->createQueryBuilder('up')
            ->where('up.value IS NOT NULL')
            ->getQuery()
            ->getResult()
        ;
        $this->assertIsArray($notNullResults);
        $this->assertCount($initialNotNullCount + 1, $notNullResults);

        // 验证我们创建的记录在结果中
        $foundOurProgress = false;
        foreach ($notNullResults as $progress) {
            $this->assertInstanceOf(UpgradeProgress::class, $progress);
            if (50 === $progress->getValue()) {
                $foundOurProgress = true;
                break;
            }
        }
        $this->assertTrue($foundOurProgress, 'Our created progress with value 50 should be in the results');
    }

    public function testCountWithNullValueQuery(): void
    {
        // 获取初始的记录数
        $em = self::getEntityManager();
        $initialNotNullQuery = $em->createQuery(
            'SELECT COUNT(up) FROM UserLevelBundle\Entity\UpgradeProgress up WHERE up.value IS NOT NULL'
        );
        $initialNotNullCount = (int) $initialNotNullQuery->getSingleScalarResult();

        $user = $this->createNormalUser('test@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progress = new UpgradeProgress();
        $progress->setUser($user);
        $progress->setUpgradeRule($upgradeRule);
        $progress->setValue(75);
        $this->repository->save($progress, true);  // 强制flush

        $nullCount = (int) $this->repository->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.value IS NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $this->assertEquals(0, $nullCount);

        $notNullCount = (int) $this->repository->createQueryBuilder('up')
            ->select('COUNT(up.id)')
            ->where('up.value IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult()
        ;
        $this->assertEquals($initialNotNullCount + 1, $notNullCount);
    }

    public function testCountByAssociationUserShouldReturnCorrectNumber(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $user3 = $this->createNormalUser('user3@example.com', 'password123');
        $user4 = $this->createNormalUser('user4@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule1 = $this->createUpgradeRule('Rule 1', 100, $level);
        $upgradeRule2 = $this->createUpgradeRule('Rule 2', 200, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule1);
        $progress1->setValue(10);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule1);
        $progress2->setValue(20);
        $this->repository->save($progress2, true);  // 强制flush

        $progress3 = new UpgradeProgress();
        $progress3->setUser($user3);
        $progress3->setUpgradeRule($upgradeRule2);
        $progress3->setValue(30);
        $this->repository->save($progress3, true);  // 强制flush

        $progress4 = new UpgradeProgress();
        $progress4->setUser($user4);
        $progress4->setUpgradeRule($upgradeRule2);
        $progress4->setValue(40);
        $this->repository->save($progress4, true);  // 强制flush

        $user1Count = $this->repository->count(['user' => $user1]);
        $this->assertEquals(1, $user1Count);

        $user2Count = $this->repository->count(['user' => $user2]);
        $this->assertEquals(1, $user2Count);

        $user3Count = $this->repository->count(['user' => $user3]);
        $this->assertEquals(1, $user3Count);

        $user4Count = $this->repository->count(['user' => $user4]);
        $this->assertEquals(1, $user4Count);
    }

    public function testCountByAssociationUpgradeRuleShouldReturnCorrectNumber(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $user3 = $this->createNormalUser('user3@example.com', 'password123');
        $user4 = $this->createNormalUser('user4@example.com', 'password123');
        $user5 = $this->createNormalUser('user5@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule1 = $this->createUpgradeRule('Rule 1', 100, $level);
        $upgradeRule2 = $this->createUpgradeRule('Rule 2', 200, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule1);
        $progress1->setValue(15);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule1);
        $progress2->setValue(30);
        $this->repository->save($progress2, true);  // 强制flush

        $progress3 = new UpgradeProgress();
        $progress3->setUser($user3);
        $progress3->setUpgradeRule($upgradeRule1);
        $progress3->setValue(45);
        $this->repository->save($progress3, true);  // 强制flush

        $progress4 = new UpgradeProgress();
        $progress4->setUser($user4);
        $progress4->setUpgradeRule($upgradeRule2);
        $progress4->setValue(25);
        $this->repository->save($progress4, true);  // 强制flush

        $progress5 = new UpgradeProgress();
        $progress5->setUser($user5);
        $progress5->setUpgradeRule($upgradeRule2);
        $progress5->setValue(50);
        $this->repository->save($progress5, true);  // 强制flush

        $rule1Count = $this->repository->count(['upgradeRule' => $upgradeRule1]);
        $this->assertEquals(3, $rule1Count);

        $rule2Count = $this->repository->count(['upgradeRule' => $upgradeRule2]);
        $this->assertEquals(2, $rule2Count);
    }

    public function testFindOneByAssociationUserShouldReturnMatchingEntity(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevel('Bronze', 1);
        $upgradeRule = $this->createUpgradeRule('Rule 1', 100, $level);

        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($upgradeRule);
        $progress1->setValue(60);
        $this->repository->save($progress1, true);  // 强制flush

        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($upgradeRule);
        $progress2->setValue(90);
        $this->repository->save($progress2, true);  // 强制flush

        $found1 = $this->repository->findOneBy(['user' => $user1]);
        $this->assertNotNull($found1);
        $this->assertEquals(60, $found1->getValue());
        $this->assertEquals($user1->getUserIdentifier(), $found1->getUser()->getUserIdentifier());

        $found2 = $this->repository->findOneBy(['user' => $user2]);
        $this->assertNotNull($found2);
        $this->assertEquals(90, $found2->getValue());
        $this->assertEquals($user2->getUserIdentifier(), $found2->getUser()->getUserIdentifier());
    }

    private function createLevel(string $title, int $level): Level
    {
        static $counter = 0;
        $counterValue = is_int($counter) ? $counter + 1 : 1;
        $counter = $counterValue;

        // 使用微秒时间戳+计数器+随机数确保唯一性
        $uniqueLevel = (int) (microtime(true) * 1000000) + $counter + mt_rand(10000, 99999);

        $levelEntity = new Level();
        $levelEntity->setTitle($title . '_' . $counter);
        $levelEntity->setLevel($uniqueLevel);
        $levelEntity->setValid(true);

        $em = self::getEntityManager();
        $em->persist($levelEntity);
        $em->flush();

        return $levelEntity;
    }

    private function createUpgradeRule(string $title, int $value, Level $level): UpgradeRule
    {
        $randomSuffix = mt_rand(1000, 9999);

        $upgradeRule = new UpgradeRule();
        $upgradeRule->setTitle($title . '_' . $randomSuffix);
        $upgradeRule->setValue($value);
        $upgradeRule->setLevel($level);
        $upgradeRule->setValid(true);

        $em = self::getEntityManager();
        $em->persist($upgradeRule);
        $em->flush();

        return $upgradeRule;
    }
}
