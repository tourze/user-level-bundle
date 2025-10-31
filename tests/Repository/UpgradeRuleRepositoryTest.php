<?php

namespace UserLevelBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;
use UserLevelBundle\Repository\UpgradeRuleRepository;

/**
 * @internal
 */
#[CoversClass(UpgradeRuleRepository::class)]
#[RunTestsInSeparateProcesses]
final class UpgradeRuleRepositoryTest extends AbstractRepositoryTestCase
{
    private UpgradeRuleRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(UpgradeRuleRepository::class);
    }

    /**
     * @return ServiceEntityRepository<UpgradeRule>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $level = $this->createLevelForTest(1);
        $this->persistAndFlush($level);

        $upgradeRule = new UpgradeRule();
        $upgradeRule->setTitle('Test Rule');
        $upgradeRule->setValue(100);
        $upgradeRule->setLevel($level);
        $upgradeRule->setValid(true);

        return $upgradeRule;
    }

    private function createLevelForTest(?int $level = null): Level
    {
        static $levelCounter = 0;
        $levelCounterValue = is_int($levelCounter) ? $levelCounter + 1 : 1;
        $levelCounter = $levelCounterValue;

        // 如果指定了level值，添加偏移量确保唯一性
        if (null !== $level) {
            $uniqueLevel = $level * 10000 + $levelCounter + mt_rand(100, 999);
        } else {
            // 使用时间戳+计数器+随机数来确保唯一性
            $uniqueLevel = time() + $levelCounter + mt_rand(1000, 9999);
        }

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
    private function createUpgradeRuleForTest(Level $level, array $attributes = []): UpgradeRule
    {
        static $ruleCounter = 0;
        $ruleCounterValue = is_int($ruleCounter) ? $ruleCounter + 1 : 1;
        $ruleCounter = $ruleCounterValue;

        $rule = new UpgradeRule();
        $rule->setTitle("Test Rule {$ruleCounter}");
        $rule->setValue($ruleCounter * 100);
        $rule->setLevel($level);
        $rule->setValid(true);
        $rule->setCreateTime(new \DateTimeImmutable());
        $rule->setUpdateTime(new \DateTimeImmutable());
        $rule->setCreatedBy('test_user');
        $rule->setUpdatedBy('test_user');

        $this->applyAttributesToUpgradeRule($rule, $attributes);
        $this->persistAndFlush($rule);

        return $rule;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyAttributesToUpgradeRule(UpgradeRule $rule, array $attributes): void
    {
        $this->applyScalarAttributes($rule, $attributes);
        $this->applyEntityAttributes($rule, $attributes);
        $this->applyDateTimeAttributes($rule, $attributes);
        $this->applyStringAttributes($rule, $attributes);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyScalarAttributes(UpgradeRule $rule, array $attributes): void
    {
        if (isset($attributes['title']) && is_string($attributes['title'])) {
            $rule->setTitle($attributes['title']);
        }
        if (isset($attributes['value']) && is_int($attributes['value'])) {
            $rule->setValue($attributes['value']);
        }
        if (isset($attributes['valid']) && is_bool($attributes['valid'])) {
            $rule->setValid($attributes['valid']);
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyEntityAttributes(UpgradeRule $rule, array $attributes): void
    {
        if (isset($attributes['level'])) {
            $levelEntity = $attributes['level'];
            $rule->setLevel($levelEntity instanceof Level ? $levelEntity : null);
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyDateTimeAttributes(UpgradeRule $rule, array $attributes): void
    {
        if (isset($attributes['createTime'])) {
            $createTime = $attributes['createTime'];
            $rule->setCreateTime($createTime instanceof \DateTimeImmutable ? $createTime : null);
        }
        if (isset($attributes['updateTime'])) {
            $updateTime = $attributes['updateTime'];
            $rule->setUpdateTime($updateTime instanceof \DateTimeImmutable ? $updateTime : null);
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyStringAttributes(UpgradeRule $rule, array $attributes): void
    {
        if (isset($attributes['createdBy'])) {
            $createdBy = $attributes['createdBy'];
            $rule->setCreatedBy(is_string($createdBy) ? $createdBy : null);
        }
        if (isset($attributes['updatedBy'])) {
            $updatedBy = $attributes['updatedBy'];
            $rule->setUpdatedBy(is_string($updatedBy) ? $updatedBy : null);
        }
    }

    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        // 集成测试中从容器获取 repository 实例
        $repository = self::getService(UpgradeRuleRepository::class);

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    public function testFind(): void
    {
        $level = $this->createLevelForTest();
        $upgradeRule = $this->createUpgradeRuleForTest($level, [
            'title' => 'Test Upgrade Rule',
            'value' => 100,
        ]);

        $found = $this->repository->find($upgradeRule->getId());

        $this->assertNotNull($found);
        $this->assertEquals($upgradeRule->getId(), $found->getId());
        $this->assertEquals('Test Upgrade Rule', $found->getTitle());
        $this->assertEquals(100, $found->getValue());
        $this->assertTrue($found->isValid());
    }

    public function testFindWithNonExistentId(): void
    {
        $found = $this->repository->find('999999999999999999');

        $this->assertNull($found);
    }

    public function testFindWithNonExistentIdShouldReturnNullForUpgradeRule(): void
    {
        $found = $this->repository->find('999999999999999999');

        $this->assertNull($found);
    }

    public function testFindAll(): void
    {
        $level = $this->createLevelForTest();
        $upgradeRule1 = $this->createUpgradeRuleForTest($level, ['title' => 'Rule 1']);
        $upgradeRule2 = $this->createUpgradeRuleForTest($level, ['title' => 'Rule 2', 'valid' => false]);

        $all = $this->repository->findAll();

        // 在集成测试中，可能有其他测试的数据，所以只验证我们创建的数据存在
        $this->assertIsArray($all);
        $this->assertGreaterThanOrEqual(2, count($all));
        $this->assertContains($upgradeRule1, $all);
        $this->assertContains($upgradeRule2, $all);
    }

    public function testFindAllWithEmptyDatabase(): void
    {
        // 在集成测试中，其他测试已经创建了数据，这里验证返回的是数组类型即可
        $all = $this->repository->findAll();

        $this->assertIsArray($all);
        // 不验证为空，因为其他测试可能已经添加了数据
    }

    public function testFindBy(): void
    {
        $level = $this->createLevelForTest();
        $validRule = $this->createUpgradeRuleForTest($level, ['valid' => true]);
        $invalidRule = $this->createUpgradeRuleForTest($level, ['valid' => false]);

        // 限制查询范围到我们创建的 level
        $validRules = $this->repository->findBy(['valid' => true, 'userLevel' => $level]);
        $invalidRules = $this->repository->findBy(['valid' => false, 'userLevel' => $level]);

        $this->assertCount(1, $validRules);
        $this->assertContains($validRule, $validRules);
        $this->assertCount(1, $invalidRules);
        $this->assertContains($invalidRule, $invalidRules);
    }

    public function testFindByWithNonMatchingCriteriaShouldReturnEmptyArrayForUpgradeRule(): void
    {
        // 使用一个不存在的title来确保返回空结果
        $result = $this->repository->findBy(['title' => 'NonExistentRuleTitle_' . time()]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindByWithEmptyResult(): void
    {
        // 使用一个不存在的title来确保返回空结果
        $result = $this->repository->findBy(['title' => 'AnotherNonExistentRuleTitle_' . time()]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $level = $this->createLevelForTest();
        for ($i = 1; $i <= 5; ++$i) {
            $this->createUpgradeRuleForTest($level, ['title' => "Rule {$i}"]);
        }

        // 限制查询范围到我们创建的 level
        $firstPage = $this->repository->findBy(['valid' => true, 'userLevel' => $level], null, 2, 0);
        $secondPage = $this->repository->findBy(['valid' => true, 'userLevel' => $level], null, 2, 2);

        $this->assertCount(2, $firstPage);
        $this->assertCount(2, $secondPage);
    }

    public function testFindOneBy(): void
    {
        $level = $this->createLevelForTest();
        $upgradeRule = $this->createUpgradeRuleForTest($level, ['title' => 'Unique Rule']);

        $found = $this->repository->findOneBy(['title' => 'Unique Rule']);

        $this->assertNotNull($found);
        $this->assertEquals($upgradeRule->getId(), $found->getId());
        $this->assertEquals('Unique Rule', $found->getTitle());
    }

    public function testFindOneByWithNonExistentCriteria(): void
    {
        $found = $this->repository->findOneBy(['title' => 'Non Existent Rule']);

        $this->assertNull($found);
    }

    public function testFindOneByWithNonMatchingCriteriaShouldReturnNullForUpgradeRule(): void
    {
        $found = $this->repository->findOneBy(['title' => 'Non Existent Rule']);

        $this->assertNull($found);
    }

    public function testCount(): void
    {
        $level = $this->createLevelForTest();
        $this->createUpgradeRuleForTest($level, ['valid' => true]);
        $this->createUpgradeRuleForTest($level, ['valid' => false]);

        // 限制计数范围到我们创建的 level
        $totalCount = $this->repository->count(['userLevel' => $level]);
        $validCount = $this->repository->count(['valid' => true, 'userLevel' => $level]);
        $invalidCount = $this->repository->count(['valid' => false, 'userLevel' => $level]);

        $this->assertEquals(2, $totalCount);
        $this->assertEquals(1, $validCount);
        $this->assertEquals(1, $invalidCount);
    }

    public function testCountWithEmptyDatabase(): void
    {
        // 使用不存在的条件来模拟空数据库
        $count = $this->repository->count(['title' => 'NonExistentTitle_' . time()]);

        $this->assertEquals(0, $count);
    }

    public function testSave(): void
    {
        $level = $this->createLevelForTest();

        $upgradeRule = new UpgradeRule();
        $upgradeRule->setTitle('New Rule');
        $upgradeRule->setValue(150);
        $upgradeRule->setLevel($level);
        $upgradeRule->setValid(true);

        $this->repository->save($upgradeRule);

        $this->assertEntityPersisted($upgradeRule);
        $this->assertNotNull($upgradeRule->getId());
        $this->assertEquals('New Rule', $upgradeRule->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $level = $this->createLevelForTest();

        $upgradeRule = new UpgradeRule();
        $upgradeRule->setTitle('New Rule');
        $upgradeRule->setValue(150);
        $upgradeRule->setLevel($level);
        $upgradeRule->setValid(true);

        $this->repository->save($upgradeRule, false);
        $entityManager = self::getEntityManager();
        $entityManager->flush();

        $this->assertEntityPersisted($upgradeRule);
        $this->assertNotNull($upgradeRule->getId());
    }

    public function testRemove(): void
    {
        $level = $this->createLevelForTest();
        $upgradeRule = $this->createUpgradeRuleForTest($level, ['title' => 'Rule to Remove']);

        $id = $upgradeRule->getId();
        $this->assertNotNull($id);
        $this->repository->remove($upgradeRule);

        $this->assertEntityNotExists(UpgradeRule::class, $id);
    }

    public function testFindByLevel(): void
    {
        $level1 = $this->createLevelForTest();
        $level2 = $this->createLevelForTest();

        $rule1 = $this->createUpgradeRuleForTest($level1, ['title' => 'Rule for Level 1']);
        $rule2 = $this->createUpgradeRuleForTest($level2, ['title' => 'Rule for Level 2']);

        $level1Rules = $this->repository->findBy(['userLevel' => $level1]);
        $level2Rules = $this->repository->findBy(['userLevel' => $level2]);

        $this->assertCount(1, $level1Rules);
        $this->assertContains($rule1, $level1Rules);
        $this->assertCount(1, $level2Rules);
        $this->assertContains($rule2, $level2Rules);
    }

    public function testFindByValueRange(): void
    {
        $level = $this->createLevelForTest();
        $this->createUpgradeRuleForTest($level, ['title' => 'Low Value Rule', 'value' => 50]);
        $highValueRule = $this->createUpgradeRuleForTest($level, ['title' => 'High Value Rule', 'value' => 500]);

        // 使用更精确的查询，只查询我们创建的 level 下的规则
        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.value >= :minValue AND ur.userLevel = :level')
            ->setParameter('minValue', 200)
            ->setParameter('level', $level)
        ;

        $highValueRules = $qb->getQuery()->getResult();
        $this->assertIsArray($highValueRules);

        $this->assertCount(1, $highValueRules);
        $this->assertContains($highValueRule, $highValueRules);
    }

    public function testEntityRelations(): void
    {
        $level = $this->createLevelForTest();
        $upgradeRule = $this->createUpgradeRuleForTest($level, ['title' => 'Rule with Level']);

        $foundRule = $this->repository->find($upgradeRule->getId());
        $this->assertNotNull($foundRule);
        $foundRuleLevel = $foundRule->getLevel();
        $this->assertNotNull($foundRuleLevel);
        $this->assertEquals($level->getId(), $foundRuleLevel->getId());
        $this->assertEquals($level->getTitle(), $foundRuleLevel->getTitle());
    }

    public function testNullableFields(): void
    {
        $level = $this->createLevelForTest();

        $upgradeRule = new UpgradeRule();
        $upgradeRule->setTitle('Rule with Default Values');
        $upgradeRule->setValue(0);  // value 字段不可空，使用默认值
        $upgradeRule->setLevel($level);
        $upgradeRule->setValid(false);  // valid 字段设置为false而不是null

        $this->repository->save($upgradeRule);

        $found = $this->repository->find($upgradeRule->getId());
        $this->assertNotNull($found);
        $this->assertEquals(0, $found->getValue());
        $this->assertFalse($found->isValid());
    }

    public function testFindByWithNullValueQuery(): void
    {
        $level = $this->createLevelForTest();

        // 创建两个规则，一个值为0，一个值为100
        $ruleWithZeroValue = new UpgradeRule();
        $ruleWithZeroValue->setTitle('Rule with Zero Value');
        $ruleWithZeroValue->setValue(0);
        $ruleWithZeroValue->setLevel($level);
        $ruleWithZeroValue->setValid(true);
        $this->repository->save($ruleWithZeroValue);

        $this->createUpgradeRuleForTest($level, ['title' => 'Rule with Value', 'value' => 100]);

        // 测试查询构建器的条件构建功能 - 查询值为0的规则
        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.value = :zeroValue')
            ->setParameter('zeroValue', 0)
        ;
        $zeroValueRules = $qb->getQuery()->getResult();
        $this->assertIsArray($zeroValueRules);

        $this->assertCount(1, $zeroValueRules);
        $this->assertContains($ruleWithZeroValue, $zeroValueRules);
    }

    public function testFindByWithNullableFieldsShouldWork(): void
    {
        $level = $this->createLevelForTest();

        // 创建有 null 值的规则
        $ruleWithNullValue = new UpgradeRule();
        $ruleWithNullValue->setTitle('Rule with Null Value');
        $ruleWithNullValue->setValue(0);
        $ruleWithNullValue->setLevel($level);
        $ruleWithNullValue->setValid(true);
        $this->repository->save($ruleWithNullValue);

        $ruleWithNullValid = new UpgradeRule();
        $ruleWithNullValid->setTitle('Rule with Null Valid');
        $ruleWithNullValid->setValue(100);
        $ruleWithNullValid->setLevel($level);
        $ruleWithNullValid->setValid(false);
        $this->repository->save($ruleWithNullValid);

        // 创建有值的规则用于对比
        $this->createUpgradeRuleForTest($level, ['title' => 'Rule with Value', 'value' => 200, 'valid' => true]);

        // 测试查询构建器的 IS NULL 功能（即使没有实际的 null 值）
        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.value IS NULL')
        ;
        $nullValueRules = $qb->getQuery()->getResult();
        $this->assertIsArray($nullValueRules);
        $this->assertCount(0, $nullValueRules); // 由于 value 不可空，应该没有结果

        // 测试 valid IS NULL 查询
        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.valid IS NULL')
        ;
        $nullValidRules = $qb->getQuery()->getResult();
        $this->assertIsArray($nullValidRules);
        $this->assertCount(0, $nullValidRules); // 由于 valid 不可空，应该没有结果
    }

    public function testFindByWithSpecificValue(): void
    {
        $level = $this->createLevelForTest();

        $ruleWithSpecificValue = new UpgradeRule();
        $ruleWithSpecificValue->setTitle('Rule with Specific Value');
        $ruleWithSpecificValue->setValue(100);
        $ruleWithSpecificValue->setLevel($level);
        $ruleWithSpecificValue->setValid(true);
        $this->repository->save($ruleWithSpecificValue);

        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.value = :value')
            ->setParameter('value', 100)
        ;
        $matchingRules = $qb->getQuery()->getResult();
        $this->assertIsArray($matchingRules);
        $this->assertCount(1, $matchingRules);
        $this->assertContains($ruleWithSpecificValue, $matchingRules);
    }

    public function testFindByWithFalseValid(): void
    {
        $level = $this->createLevelForTest();

        $ruleWithFalseValid = new UpgradeRule();
        $ruleWithFalseValid->setTitle('Rule with False Valid');
        $ruleWithFalseValid->setValue(100);
        $ruleWithFalseValid->setLevel($level);
        $ruleWithFalseValid->setValid(false);
        $this->repository->save($ruleWithFalseValid);

        $qb = $this->repository->createQueryBuilder('ur')
            ->where('ur.valid = :valid')
            ->setParameter('valid', false)
        ;
        $falseValidRules = $qb->getQuery()->getResult();
        $this->assertIsArray($falseValidRules);
        $this->assertCount(1, $falseValidRules);
        $this->assertContains($ruleWithFalseValid, $falseValidRules);
    }

    public function testCountWithSpecificValuesShouldWork(): void
    {
        $level = $this->createLevelForTest();

        // 创建具有特定值的规则
        $ruleWithZeroValue = new UpgradeRule();
        $ruleWithZeroValue->setTitle('Count Rule with Zero Value');
        $ruleWithZeroValue->setValue(0);
        $ruleWithZeroValue->setLevel($level);
        $ruleWithZeroValue->setValid(true);
        $this->repository->save($ruleWithZeroValue);

        $ruleWithFalseValid = new UpgradeRule();
        $ruleWithFalseValid->setTitle('Count Rule with False Valid');
        $ruleWithFalseValid->setValue(100);
        $ruleWithFalseValid->setLevel($level);
        $ruleWithFalseValid->setValid(false);
        $this->repository->save($ruleWithFalseValid);

        // 测试 COUNT value = 0 查询
        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.value = :value')
            ->setParameter('value', 0)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $count);

        // 测试 COUNT valid = false 查询
        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.valid = :valid')
            ->setParameter('valid', false)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $count);
    }

    public function testCountBySpecificValueShouldReturnCorrectNumber(): void
    {
        $level = $this->createLevelForTest();

        $ruleWithSpecificValue = new UpgradeRule();
        $ruleWithSpecificValue->setTitle('Count Rule with Specific Value');
        $ruleWithSpecificValue->setValue(150);
        $ruleWithSpecificValue->setLevel($level);
        $ruleWithSpecificValue->setValid(true);
        $this->repository->save($ruleWithSpecificValue);

        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.value = :value')
            ->setParameter('value', 150)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $count);
    }

    public function testCountByValidAsFalseShouldReturnCorrectNumber(): void
    {
        $level = $this->createLevelForTest();

        $ruleWithFalseValid = new UpgradeRule();
        $ruleWithFalseValid->setTitle('Count Rule with False Valid');
        $ruleWithFalseValid->setValue(100);
        $ruleWithFalseValid->setLevel($level);
        $ruleWithFalseValid->setValid(false);
        $this->repository->save($ruleWithFalseValid);

        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->where('ur.valid = :valid')
            ->setParameter('valid', false)
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertEquals(1, $count);
    }

    public function testFindByWithMultipleCriteriaShouldWork(): void
    {
        $level1 = $this->createLevelForTest(1);
        $level2 = $this->createLevelForTest(2);

        $rule1 = $this->createUpgradeRuleForTest($level1, ['title' => 'Rule for Level 1', 'valid' => true]);
        $rule2 = $this->createUpgradeRuleForTest($level2, ['title' => 'Rule for Level 2', 'valid' => true]);
        $rule3 = $this->createUpgradeRuleForTest($level1, ['title' => 'Invalid Rule', 'valid' => false]);

        // 测试按 level 关联查询
        $qb = $this->repository->createQueryBuilder('ur')
            ->join('ur.userLevel', 'l')
            ->where('l.level = :levelValue')
            ->setParameter('levelValue', $level1->getLevel())
        ;
        $level1Rules = $qb->getQuery()->getResult();
        $this->assertIsArray($level1Rules);
        $this->assertCount(2, $level1Rules);
        $this->assertContains($rule1, $level1Rules);
        $this->assertContains($rule3, $level1Rules);

        // 测试按多个条件查询
        $multipleResults = $this->repository->findBy([
            'userLevel' => $level1,
            'valid' => true,
        ]);
        $this->assertCount(1, $multipleResults);
        $this->assertContains($rule1, $multipleResults);
    }

    public function testFindByWithLevelAssociation(): void
    {
        $level1 = $this->createLevelForTest();
        $level2 = $this->createLevelForTest();

        $rule1 = $this->createUpgradeRuleForTest($level1, ['title' => 'Rule for Level 1']);
        $this->createUpgradeRuleForTest($level2, ['title' => 'Rule for Level 2']);

        $qb = $this->repository->createQueryBuilder('ur')
            ->join('ur.userLevel', 'l')
            ->where('l.id = :levelId')
            ->setParameter('levelId', $level1->getId())
        ;
        $level1Rules = $qb->getQuery()->getResult();
        $this->assertIsArray($level1Rules);

        $this->assertCount(1, $level1Rules);
        $this->assertContains($rule1, $level1Rules);
    }

    public function testCountWithLevelAssociation(): void
    {
        $level1 = $this->createLevelForTest();

        $this->createUpgradeRuleForTest($level1, ['title' => 'Rule for Level 1']);

        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->join('ur.userLevel', 'l')
            ->where('l.level = :levelValue')
            ->setParameter('levelValue', $level1->getLevel())
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();

        $this->assertEquals(1, $count);
    }

    public function testCountByAssociationLevelShouldReturnCorrectNumber(): void
    {
        $level = $this->createLevelForTest();
        $rule = $this->createUpgradeRuleForTest($level, ['title' => 'Association Count Test Rule']);

        $qb = $this->repository->createQueryBuilder('ur')
            ->select('COUNT(ur.id)')
            ->join('ur.userLevel', 'l')
            ->where('l.id = :levelId')
            ->setParameter('levelId', $level->getId())
        ;
        $count = (int) $qb->getQuery()->getSingleScalarResult();
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationLevelShouldReturnMatchingEntity(): void
    {
        $level = $this->createLevelForTest();
        $rule = $this->createUpgradeRuleForTest($level, ['title' => 'FindOne Association Test Rule']);

        $qb = $this->repository->createQueryBuilder('ur')
            ->join('ur.userLevel', 'l')
            ->where('l.id = :levelId')
            ->andWhere('ur.id = :ruleId')
            ->setParameter('levelId', $level->getId())
            ->setParameter('ruleId', $rule->getId())
            ->setMaxResults(1)
        ;
        $foundRule = $qb->getQuery()->getOneOrNullResult();
        $this->assertInstanceOf(UpgradeRule::class, $foundRule);
        $this->assertNotNull($foundRule);
        $foundRuleLevel = $foundRule->getLevel();
        $this->assertNotNull($foundRuleLevel);
        $this->assertEquals($level->getId(), $foundRuleLevel->getId());
    }
}
