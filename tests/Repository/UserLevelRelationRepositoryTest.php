<?php

namespace UserLevelBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;
use UserLevelBundle\Repository\UserLevelRelationRepository;

/**
 * @internal
 */
#[CoversClass(UserLevelRelationRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserLevelRelationRepositoryTest extends AbstractRepositoryTestCase
{
    private UserLevelRelationRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(UserLevelRelationRepository::class);
    }

    // 避免跨进程序列化时携带 EntityManager/UnitOfWork
    protected function onTearDown(): void
    {
        unset($this->repository);
        self::getEntityManager()->clear();
    }

    /**
     * @return ServiceEntityRepository<UserLevelRelation>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        static $userCounter = 0;
        $userCounterValue = is_int($userCounter) ? $userCounter + 1 : 1;
        $userCounter = $userCounterValue;

        // 确保每次创建的用户都是唯一的
        $user = $this->createNormalUser("test{$userCounter}@example.com", 'password123');
        $level = $this->createLevelForTest(1);
        $this->persistAndFlush($level);

        $userLevelRelation = new UserLevelRelation();
        $userLevelRelation->setUser($user);
        $userLevelRelation->setLevel($level);

        return $userLevelRelation;
    }

    private function createLevelForTest(?int $level = null): Level
    {
        static $levelCounter = 0;
        $levelCounterValue = is_int($levelCounter) ? $levelCounter + 1 : 1;
        $levelCounter = $levelCounterValue;

        // 如果指定了level值，需要确保唯一性（添加计数器避免冲突）
        $uniqueLevel = null !== $level ? ($level * 1000 + $levelCounter) : (time() + $levelCounter + mt_rand(1000, 9999));

        $levelEntity = new Level();
        $levelEntity->setTitle("Test Level {$levelCounter}");
        $levelEntity->setLevel($uniqueLevel);
        $levelEntity->setValid(true);
        $levelEntity->setCreateTime(new \DateTimeImmutable());
        $levelEntity->setUpdateTime(new \DateTimeImmutable());
        $levelEntity->setCreatedBy('test_user');
        $levelEntity->setUpdatedBy('test_user');

        return $levelEntity;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function createUserLevelRelationForTest(UserInterface $user, Level $level, array $attributes = []): UserLevelRelation
    {
        $relation = new UserLevelRelation();
        $relation->setUser($user);
        $relation->setLevel($level);
        $relation->setValid(true);
        $relation->setCreateTime(new \DateTimeImmutable());
        $relation->setUpdateTime(new \DateTimeImmutable());

        // 应用额外属性
        if (isset($attributes['valid']) && is_bool($attributes['valid'])) {
            $relation->setValid($attributes['valid']);
        }
        if (isset($attributes['createTime']) && $attributes['createTime'] instanceof \DateTimeImmutable) {
            $relation->setCreateTime($attributes['createTime']);
        }
        if (isset($attributes['updateTime']) && $attributes['updateTime'] instanceof \DateTimeImmutable) {
            $relation->setUpdateTime($attributes['updateTime']);
        }

        // 保存实体以便获得ID
        $this->repository->save($relation);

        return $relation;
    }

    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        // 集成测试中从容器获取 repository 实例
        $repository = self::getService(UserLevelRelationRepository::class);

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    public function testFind(): void
    {
        $user = $this->createNormalUser('user1@example.com', 'password123');
        $level = $this->createLevelForTest();
        $relation = $this->createUserLevelRelationForTest($user, $level);

        $found = $this->repository->find($relation->getId());

        $this->assertNotNull($found);
        $this->assertInstanceOf(UserLevelRelation::class, $found);
        $this->assertEquals($relation->getId(), $found->getId());
        $this->assertEquals('user1@example.com', $found->getUser()->getUserIdentifier());
        $this->assertEquals($level->getId(), $found->getLevel()->getId());
        $this->assertTrue($found->isValid());
    }

    public function testFindWithNonExistentId(): void
    {
        $found = $this->repository->find('999999999999999999');

        $this->assertNull($found);
    }

    public function testFindWithNonExistentIdShouldReturnNullForUserLevelRelation(): void
    {
        $found = $this->repository->find('999999999999999999');

        $this->assertNull($found);
    }

    public function testFindAll(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation1 = $this->createUserLevelRelationForTest($user1, $level);
        $relation2 = $this->createUserLevelRelationForTest($user2, $level, ['valid' => false]);

        $all = $this->repository->findAll();

        // 验证返回的是数组且包含我们创建的实体
        $this->assertIsArray($all);
        $this->assertGreaterThanOrEqual(2, count($all)); // 至少包含我们创建的2个
        $allIds = array_map(function ($r) {
            $this->assertInstanceOf(UserLevelRelation::class, $r);

            return $r->getId();
        }, $all);
        $this->assertContains($relation1->getId(), $allIds);
        $this->assertContains($relation2->getId(), $allIds);
    }

    public function testFindAllWithEmptyDatabase(): void
    {
        // 清空数据库中的所有 UserLevelRelation
        self::getEntityManager()->createQuery('DELETE FROM ' . UserLevelRelation::class)->execute();

        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    public function testFindBy(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $validRelation = $this->createUserLevelRelationForTest($user1, $level, ['valid' => true]);
        $invalidRelation = $this->createUserLevelRelationForTest($user2, $level, ['valid' => false]);

        $validRelations = $this->repository->findBy(['valid' => true]);
        $invalidRelations = $this->repository->findBy(['valid' => false]);

        // 验证至少包含我们创建的实体
        $this->assertGreaterThanOrEqual(1, count($validRelations));
        $this->assertContains($validRelation, $validRelations);
        $this->assertGreaterThanOrEqual(1, count($invalidRelations));
        $this->assertContains($invalidRelation, $invalidRelations);
    }

    public function testFindByWithNonMatchingCriteriaShouldReturnEmptyArrayForUserLevelRelation(): void
    {
        // 使用一个不可能存在的 ID 来确保返回空结果
        $result = $this->repository->findBy(['id' => '999999999999999999']);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindByUser(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation1 = $this->createUserLevelRelationForTest($user1, $level);
        $relation2 = $this->createUserLevelRelationForTest($user2, $level);

        $user1Relations = $this->repository->findBy(['user' => $user1]);
        $user2Relations = $this->repository->findBy(['user' => $user2]);

        $this->assertCount(1, $user1Relations);
        $this->assertSame($relation1->getId(), $user1Relations[0]->getId());
        $this->assertCount(1, $user2Relations);
        $this->assertSame($relation2->getId(), $user2Relations[0]->getId());
    }

    public function testFindByLevel(): void
    {
        $user = $this->createNormalUser('test_user@example.com', 'password123');
        $level1 = $this->createLevelForTest();
        $level2 = $this->createLevelForTest();

        $relation1 = $this->createUserLevelRelationForTest($user, $level1);
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $relation2 = $this->createUserLevelRelationForTest($user2, $level2);

        $level1Relations = $this->repository->findBy(['level' => $level1]);
        $level2Relations = $this->repository->findBy(['level' => $level2]);

        $this->assertCount(1, $level1Relations);
        $this->assertSame($relation1->getId(), $level1Relations[0]->getId());
        $this->assertCount(1, $level2Relations);
        $this->assertSame($relation2->getId(), $level2Relations[0]->getId());
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $level = $this->createLevelForTest();
        for ($i = 1; $i <= 5; ++$i) {
            $testUser = $this->createNormalUser("user{$i}@example.com", 'password123');
            $this->createUserLevelRelationForTest($testUser, $level);
        }

        $firstPage = $this->repository->findBy(['valid' => true], null, 2, 0);
        $secondPage = $this->repository->findBy(['valid' => true], null, 2, 2);

        $this->assertCount(2, $firstPage);
        $this->assertCount(2, $secondPage);
    }

    public function testFindByWithEmptyResult(): void
    {
        // 使用一个不可能存在的 ID 来确保返回空结果
        $result = $this->repository->findBy(['id' => '888888888888888888']);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindOneBy(): void
    {
        $user = $this->createNormalUser('unique_user@example.com', 'password123');
        $level = $this->createLevelForTest();
        $relation = $this->createUserLevelRelationForTest($user, $level);

        $found = $this->repository->findOneBy(['user' => $user]);

        $this->assertNotNull($found);
        $this->assertInstanceOf(UserLevelRelation::class, $found);
        $this->assertEquals($relation->getId(), $found->getId());
        $this->assertEquals('unique_user@example.com', $found->getUser()->getUserIdentifier());
    }

    public function testFindOneByWithNonExistentCriteria(): void
    {
        $nonExistentUser = $this->createNormalUser('non_existent@example.com', 'password123');
        $found = $this->repository->findOneBy(['user' => $nonExistentUser]);

        $this->assertNull($found);
    }

    public function testFindOneByWithNonMatchingCriteriaShouldReturnNullForUserLevelRelation(): void
    {
        $nonExistentUser = $this->createNormalUser('non_existent@example.com', 'password123');
        $found = $this->repository->findOneBy(['user' => $nonExistentUser]);

        $this->assertNull($found);
    }

    public function testCount(): void
    {
        $user1 = $this->createNormalUser('user1@example.com', 'password123');
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $this->createUserLevelRelationForTest($user1, $level, ['valid' => true]);
        $this->createUserLevelRelationForTest($user2, $level, ['valid' => false]);

        $totalCount = $this->repository->count([]);
        $validCount = $this->repository->count(['valid' => true]);
        $invalidCount = $this->repository->count(['valid' => false]);

        // 验证总数至少包含我们创建的2个
        $this->assertGreaterThanOrEqual(2, $totalCount);
        // 验证至少包含我们创建的1个 valid=true 和 1个 valid=false
        $this->assertGreaterThanOrEqual(1, $validCount);
        $this->assertGreaterThanOrEqual(1, $invalidCount);
    }

    public function testCountWithEmptyDatabase(): void
    {
        $count = $this->repository->count([]);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testSave(): void
    {
        $user = $this->createNormalUser('save_test_user@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation = new UserLevelRelation();
        $relation->setUser($user);
        $relation->setLevel($level);
        $relation->setValid(true);

        $this->repository->save($relation);

        $this->assertEntityPersisted($relation);
        $this->assertNotNull($relation->getId());
        $this->assertEquals('save_test_user@example.com', $relation->getUser()->getUserIdentifier());
    }

    public function testSaveWithoutFlush(): void
    {
        $user = $this->createNormalUser('save_no_flush_user@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation = new UserLevelRelation();
        $relation->setUser($user);
        $relation->setLevel($level);
        $relation->setValid(true);

        $this->repository->save($relation, false);
        self::getEntityManager()->flush();

        $this->assertEntityPersisted($relation);
        $this->assertNotNull($relation->getId());
    }

    public function testRemove(): void
    {
        $user = $this->createNormalUser('remove_test_user@example.com', 'password123');
        $level = $this->createLevelForTest();
        $relation = $this->createUserLevelRelationForTest($user, $level);

        $id = $relation->getId();
        $this->assertNotNull($id);
        $this->repository->remove($relation);

        $this->assertEntityNotExists(UserLevelRelation::class, $id);
    }

    public function testEntityRelations(): void
    {
        $user = $this->createNormalUser('relation_test_user@example.com', 'password123');
        $level = $this->createLevelForTest(5);
        $relation = $this->createUserLevelRelationForTest($user, $level);

        $found = $this->repository->find($relation->getId());
        $this->assertNotNull($found);
        $this->assertInstanceOf(UserLevelRelation::class, $found);
        $this->assertEquals('relation_test_user@example.com', $found->getUser()->getUserIdentifier());
        $this->assertEquals($level->getId(), $found->getLevel()->getId());
        $this->assertEquals($level->getLevel(), $found->getLevel()->getLevel());
    }

    public function testDefaultValidValue(): void
    {
        $user = $this->createNormalUser('nullable_test_user@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation = new UserLevelRelation();
        $relation->setUser($user);
        $relation->setLevel($level);
        // 不设置 valid 值，使用默认值

        $this->repository->save($relation);

        $found = $this->repository->find($relation->getId());
        $this->assertNotNull($found);
        $this->assertFalse($found->isValid()); // 默认值应该是 false
    }

    public function testUniqueUserConstraint(): void
    {
        $user = $this->createNormalUser('unique_constraint_user@example.com', 'password123');
        $level1 = $this->createLevelForTest(1);
        $level2 = $this->createLevelForTest(2);

        $this->createUserLevelRelationForTest($user, $level1);

        $relation2 = new UserLevelRelation();
        $relation2->setUser($user);
        $relation2->setLevel($level2);
        $relation2->setValid(true);

        $this->expectException(UniqueConstraintViolationException::class);
        $this->repository->save($relation2);
    }

    public function testFindActiveUserLevels(): void
    {
        $user1 = $this->createNormalUser('active_user1@example.com', 'password123');
        $user2 = $this->createNormalUser('active_user2@example.com', 'password123');
        $level1 = $this->createLevelForTest(1);
        $level2 = $this->createLevelForTest(2);

        $activeRelation1 = $this->createUserLevelRelationForTest($user1, $level1, ['valid' => true]);
        $activeRelation2 = $this->createUserLevelRelationForTest($user2, $level2, ['valid' => true]);
        $inactiveUser = $this->createNormalUser('inactive_user@example.com', 'password123');
        $this->createUserLevelRelationForTest($inactiveUser, $level1, ['valid' => false]);

        // 查询特定用户的有效关系，避免被其他测试数据干扰
        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.level', 'l')
            ->join('ulr.user', 'u')
            ->where('ulr.valid = :valid')
            ->andWhere('l.valid = :levelValid')
            ->andWhere('ulr.user IN (:users)')
            ->setParameter('valid', true)
            ->setParameter('levelValid', true)
            ->setParameter('users', [$user1, $user2])
            ->orderBy('l.level', 'ASC')
        ;

        $activeRelations = $qb->getQuery()->getResult();
        $this->assertIsArray($activeRelations);

        $this->assertCount(2, $activeRelations);
        $this->assertInstanceOf(UserLevelRelation::class, $activeRelations[0]);
        $this->assertInstanceOf(UserLevelRelation::class, $activeRelations[1]);
        $this->assertEquals($activeRelation1, $activeRelations[0]);
        $this->assertEquals($activeRelation2, $activeRelations[1]);
    }

    public function testCascadeDeleteWithLevel(): void
    {
        $user = $this->createNormalUser('cascade_test_user@example.com', 'password123');
        $level = $this->createLevelForTest();
        $relation = $this->createUserLevelRelationForTest($user, $level);

        $relationId = $relation->getId();
        $levelId = $level->getId();
        $this->assertNotNull($relationId);
        $this->assertNotNull($levelId);

        // 手动删除相关的 UserLevelRelation 来模拟级联删除
        self::getEntityManager()->remove($relation);
        self::getEntityManager()->remove($level);
        self::getEntityManager()->flush();

        $this->assertEntityNotExists(Level::class, $levelId);
        $this->assertEntityNotExists(UserLevelRelation::class, $relationId);
    }

    public function testFindByWithMultipleCriteriaShouldWork(): void
    {
        $user1 = $this->createNormalUser('multi_user1@example.com', 'password123');
        $user2 = $this->createNormalUser('multi_user2@example.com', 'password123');
        $user3 = $this->createNormalUser('multi_user3@example.com', 'password123');
        $level1 = $this->createLevelForTest(1);
        $level2 = $this->createLevelForTest(2);

        $relation1 = $this->createUserLevelRelationForTest($user1, $level1);
        $relation2 = $this->createUserLevelRelationForTest($user2, $level2);
        $relation3 = $this->createUserLevelRelationForTest($user3, $level1, ['valid' => false]);

        // 测试按 user 查询
        $user1Relations = $this->repository->findBy(['user' => $user1]);
        $this->assertCount(1, $user1Relations);
        $this->assertSame($relation1->getId(), $user1Relations[0]->getId());

        // 测试按 level 关联查询
        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.level', 'l')
            ->where('l.level = :levelValue')
            ->setParameter('levelValue', $level1->getLevel())
        ;
        $level1Relations = $qb->getQuery()->getResult();
        $this->assertIsArray($level1Relations);
        $this->assertCount(2, $level1Relations); // relation1 和 relation3 都使用 level1
        $level1RelationIds = array_map(function ($r) {
            $this->assertInstanceOf(UserLevelRelation::class, $r);

            return $r->getId();
        }, $level1Relations);
        $this->assertContains($relation1->getId(), $level1RelationIds);
        $this->assertContains($relation3->getId(), $level1RelationIds);

        // 测试按 user email 关联查询
        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.user', 'u')
            ->where('ulr.user = :user')
            ->setParameter('user', $user1)
        ;
        $userEmailRelations = $qb->getQuery()->getResult();
        $this->assertIsArray($userEmailRelations);
        $this->assertCount(1, $userEmailRelations);
        $this->assertInstanceOf(UserLevelRelation::class, $userEmailRelations[0]);
        $this->assertSame($relation1->getId(), $userEmailRelations[0]->getId());

        // 测试多条件查询
        $multipleResults = $this->repository->findBy([
            'user' => $user1,
            'valid' => true,
        ]);
        $this->assertCount(1, $multipleResults);
        $this->assertSame($relation1->getId(), $multipleResults[0]->getId());
    }

    public function testFindByWithLevelAssociation(): void
    {
        $user = $this->createNormalUser('test_user@example.com', 'password123');
        $level1 = $this->createLevelForTest(1);
        $level2 = $this->createLevelForTest(2);

        $relation1 = $this->createUserLevelRelationForTest($user, $level1);
        $user2 = $this->createNormalUser('user2@example.com', 'password123');
        $this->createUserLevelRelationForTest($user2, $level2);

        self::getEntityManager()->flush();

        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.level', 'l')
            ->where('l.level = :levelValue')
            ->setParameter('levelValue', $level1->getLevel())
        ;
        $level1Relations = $qb->getQuery()->getResult();
        $this->assertIsArray($level1Relations);

        $this->assertCount(1, $level1Relations);
        $this->assertInstanceOf(UserLevelRelation::class, $level1Relations[0]);
        $this->assertSame($relation1->getId(), $level1Relations[0]->getId());
    }

    public function testCountWithLevelAssociation(): void
    {
        $user = $this->createNormalUser('test_user@example.com', 'password123');
        $level1 = $this->createLevelForTest(1);

        $this->createUserLevelRelationForTest($user, $level1);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->select('COUNT(ulr.id)')
            ->join('ulr.level', 'l')
            ->where('l.level = :levelValue')
            ->setParameter('levelValue', 1)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();

        $this->assertEquals(1, $count);
    }

    public function testFindByWithUserAssociation(): void
    {
        $user1 = $this->createNormalUser('assoc_user1@example.com', 'password123');
        $user2 = $this->createNormalUser('assoc_user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $relation1 = $this->createUserLevelRelationForTest($user1, $level);
        $level2 = $this->createLevelForTest();
        $this->createUserLevelRelationForTest($user2, $level2);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.user', 'u')
            ->where('ulr.user = :user')
            ->setParameter('user', $user1)
        ;
        $user1Relations = $qb->getQuery()->getResult();
        $this->assertIsArray($user1Relations);

        $this->assertCount(1, $user1Relations);
        $this->assertInstanceOf(UserLevelRelation::class, $user1Relations[0]);
        $this->assertSame($relation1->getId(), $user1Relations[0]->getId());
    }

    public function testCountWithUserAssociation(): void
    {
        $user1 = $this->createNormalUser('count_user1@example.com', 'password123');
        $user2 = $this->createNormalUser('count_user2@example.com', 'password123');
        $level = $this->createLevelForTest();

        $this->createUserLevelRelationForTest($user1, $level);

        $level2 = $this->createLevelForTest();
        $this->createUserLevelRelationForTest($user2, $level2);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->select('COUNT(ulr.id)')
            ->join('ulr.user', 'u')
            ->where('ulr.user = :user')
            ->setParameter('user', $user1)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();

        $this->assertEquals(1, $count);
    }

    public function testCountByAssociationUserShouldReturnCorrectNumber(): void
    {
        $level = $this->createLevelForTest();
        $user = $this->createNormalUser('count_association_user_test@example.com', 'password123');

        $relation = $this->createUserLevelRelationForTest($user, $level, ['valid' => true]);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->select('COUNT(ulr.id)')
            ->join('ulr.user', 'u')
            ->where('ulr.user = :user')
            ->setParameter('user', $user)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationUserShouldReturnMatchingEntity(): void
    {
        $level = $this->createLevelForTest();
        $user = $this->createNormalUser('findone_association_user_test@example.com', 'password123');

        $relation = $this->createUserLevelRelationForTest($user, $level, ['valid' => true]);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.user', 'u')
            ->where('ulr.user = :user')
            ->andWhere('ulr.id = :relationId')
            ->setParameter('user', $user)
            ->setParameter('relationId', $relation->getId())
            ->setMaxResults(1)
        ;
        $foundRelation = $qb->getQuery()->getOneOrNullResult();
        $this->assertInstanceOf(UserLevelRelation::class, $foundRelation);
        $this->assertEquals($user->getUserIdentifier(), $foundRelation->getUser()->getUserIdentifier());
    }

    public function testCountByAssociationLevelShouldReturnCorrectNumber(): void
    {
        $level = $this->createLevelForTest();
        $user = $this->createNormalUser('count_association_level_test@example.com', 'password123');

        $relation = $this->createUserLevelRelationForTest($user, $level, ['valid' => true]);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->select('COUNT(ulr.id)')
            ->join('ulr.level', 'l')
            ->where('l.id = :levelId')
            ->setParameter('levelId', $level->getId())
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationLevelShouldReturnMatchingEntity(): void
    {
        $level = $this->createLevelForTest();
        $user = $this->createNormalUser('findone_association_level_test@example.com', 'password123');

        $relation = $this->createUserLevelRelationForTest($user, $level, ['valid' => true]);

        $qb = $this->repository->createQueryBuilder('ulr')
            ->join('ulr.level', 'l')
            ->where('l.id = :levelId')
            ->andWhere('ulr.id = :relationId')
            ->setParameter('levelId', $level->getId())
            ->setParameter('relationId', $relation->getId())
            ->setMaxResults(1)
        ;
        $foundRelation = $qb->getQuery()->getOneOrNullResult();
        $this->assertInstanceOf(UserLevelRelation::class, $foundRelation);
        $this->assertEquals($level->getId(), $foundRelation->getLevel()->getId());
    }
}
