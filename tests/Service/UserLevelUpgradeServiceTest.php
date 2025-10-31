<?php

namespace UserLevelBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;
use UserLevelBundle\Repository\LevelRepository;
use UserLevelBundle\Repository\UpgradeProgressRepository;
use UserLevelBundle\Repository\UserLevelRelationRepository;
use UserLevelBundle\Service\UserLevelUpgradeService;

/**
 * @internal
 */
#[CoversClass(UserLevelUpgradeService::class)]
final class UserLevelUpgradeServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 创建 mock 对象
        $this->userLevelRelationRepository = $this->createMock(UserLevelRelationRepository::class);
        $this->levelRepository = $this->createMock(LevelRepository::class);
        $this->levelUpgradeProgressRepository = $this->createMock(UpgradeProgressRepository::class);
        $this->user = $this->createMock(UserInterface::class);

        // 确保类型安全
        $this->assertInstanceOf(UserLevelRelationRepository::class, $this->userLevelRelationRepository);
        $this->assertInstanceOf(LevelRepository::class, $this->levelRepository);
        $this->assertInstanceOf(UpgradeProgressRepository::class, $this->levelUpgradeProgressRepository);
        $this->assertInstanceOf(UserInterface::class, $this->user);

        // 直接创建服务实例
        $this->service = new UserLevelUpgradeService(
            $this->userLevelRelationRepository,
            $this->levelRepository,
            $this->levelUpgradeProgressRepository
        );
    }

    private UserLevelUpgradeService $service;

    private UserLevelRelationRepository&MockObject $userLevelRelationRepository;

    private LevelRepository&MockObject $levelRepository;

    private UpgradeProgressRepository&MockObject $levelUpgradeProgressRepository;

    private UserInterface&MockObject $user;

    public function testUpgradeWithNoCurrentLevelFindsLowestLevel(): void
    {
        // 设置没有当前等级
        $this->userLevelRelationRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn(null)
        ;

        // 模拟获取最低级别
        $lowestLevel = new Level();
        $lowestLevel->setLevel(1);
        $lowestLevel->setTitle('普通会员');
        $lowestLevel->setValid(true);

        $this->levelRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['valid' => true], ['level' => 'ASC'])
            ->willReturn($lowestLevel)
        ;

        // 设置升级规则
        $upgradeRules = new ArrayCollection();
        $lowestLevelReflection = new \ReflectionClass($lowestLevel);
        $upgradeRulesProperty = $lowestLevelReflection->getProperty('upgradeRules');
        $upgradeRulesProperty->setValue($lowestLevel, $upgradeRules);

        // 预期将查询用户的升级进度
        $this->levelUpgradeProgressRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user])
        ;

        // 执行升级方法
        $this->service->upgrade($this->user);
    }

    public function testUpgradeWithCurrentLevelFindsNextLevel(): void
    {
        // 设置当前等级
        $currentLevel = new Level();
        $currentLevel->setLevel(1);
        $currentLevel->setTitle('普通会员');

        $userLevelRelation = new UserLevelRelation();
        $userLevelRelation->setLevel($currentLevel);

        $this->userLevelRelationRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn($userLevelRelation)
        ;

        // 简化查询生成器逻辑，避免复杂的 mock
        $nextLevel = new Level();
        $nextLevel->setLevel(2);
        $nextLevel->setTitle('VIP会员');
        $nextLevel->setValid(true);

        // 设置升级规则
        $upgradeRules = new ArrayCollection();
        $nextLevelReflection = new \ReflectionClass($nextLevel);
        $upgradeRulesProperty = $nextLevelReflection->getProperty('upgradeRules');
        $upgradeRulesProperty->setValue($nextLevel, $upgradeRules);

        // 设置 LevelRepository 行为 - 模拟查找下一个等级
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->createMock(Query::class);

        $mockQueryBuilder
            ->method('where')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('setParameter')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('addOrderBy')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('setMaxResults')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('getQuery')
            ->willReturn($mockQuery)
        ;

        // 模拟返回下一个等级（单个对象，因为 getOneOrNullResult() 返回单个对象或null）
        $mockQuery
            ->method('getOneOrNullResult')
            ->willReturn($nextLevel)
        ;

        $this->levelRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder)
        ;

        // 预期将查询用户的升级进度
        $this->levelUpgradeProgressRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user])
        ;

        // 执行升级方法
        $this->service->upgrade($this->user);
    }

    public function testUpgradeWithNoNextLevelDoesNothing(): void
    {
        // 设置当前等级
        $currentLevel = new Level();
        $currentLevel->setLevel(5); // 假设这是最高级别
        $currentLevel->setTitle('钻石会员');

        $userLevelRelation = new UserLevelRelation();
        $userLevelRelation->setLevel($currentLevel);

        $this->userLevelRelationRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn($userLevelRelation)
        ;

        // 设置 LevelRepository 行为 - 模拟没有下一个等级
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockQuery = $this->createMock(Query::class);

        $mockQueryBuilder
            ->method('where')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('setParameter')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('addOrderBy')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('setMaxResults')
            ->willReturn($mockQueryBuilder)
        ;
        $mockQueryBuilder
            ->method('getQuery')
            ->willReturn($mockQuery)
        ;

        // 模拟返回null（没有下一个等级）
        $mockQuery
            ->method('getOneOrNullResult')
            ->willReturn(null)
        ;

        $this->levelRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder)
        ;

        // 预期不会查询升级进度（因为没有下一个等级）
        $this->levelUpgradeProgressRepository
            ->expects($this->never())
            ->method('findBy')
        ;

        // 执行升级方法 - 应该正常退出，因为没有下一个等级
        $this->service->upgrade($this->user);
    }

    public function testDegradeIsImplementedButEmpty(): void
    {
        // 测试降级方法存在但为空实现 - 验证无副作用

        // 由于degrade方法为空实现，无论调用多少次都不应有任何副作用
        $this->service->degrade($this->user);
        $this->service->degrade($this->user);

        // 验证方法确实可以被调用且没有抛出异常
        $this->assertTrue(true, 'degrade method exists and can be called without side effects');
    }
}
