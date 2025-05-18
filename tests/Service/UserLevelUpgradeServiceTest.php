<?php

namespace UserLevelBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;
use UserLevelBundle\Repository\LevelRepository;
use UserLevelBundle\Repository\UpgradeProgressRepository;
use UserLevelBundle\Repository\UserLevelRelationRepository;
use UserLevelBundle\Service\UserLevelUpgradeService;

class UserLevelUpgradeServiceTest extends TestCase
{
    private UserLevelUpgradeService $service;
    private MockObject|UserLevelRelationRepository $userLevelRelationRepository;
    private MockObject|LevelRepository $levelRepository;
    private MockObject|UpgradeProgressRepository $levelUpgradeProgressRepository;
    private MockObject|UserInterface $user;

    protected function setUp(): void
    {
        $this->userLevelRelationRepository = $this->createMock(UserLevelRelationRepository::class);
        $this->levelRepository = $this->createMock(LevelRepository::class);
        $this->levelUpgradeProgressRepository = $this->createMock(UpgradeProgressRepository::class);
        $this->user = $this->getMockBuilder(ServiceTestUserInterface::class)
            ->getMock();

        $this->service = new UserLevelUpgradeService(
            $this->userLevelRelationRepository,
            $this->levelRepository,
            $this->levelUpgradeProgressRepository
        );
    }

    public function testUpgrade_withNoCurrentLevel_findsLowestLevel(): void
    {
        // 设置没有当前等级
        $this->userLevelRelationRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $this->user])
            ->willReturn(null);

        // 模拟获取最低级别
        $lowestLevel = new Level();
        $lowestLevel->setLevel(1);
        $lowestLevel->setTitle('普通会员');
        $lowestLevel->setValid(true);
        
        $this->levelRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['valid' => true], ['level' => 'ASC'])
            ->willReturn($lowestLevel);
            
        // 设置升级规则
        $upgradeRules = new ArrayCollection();
        $lowestLevelReflection = new \ReflectionClass($lowestLevel);
        $upgradeRulesProperty = $lowestLevelReflection->getProperty('upgradeRules');
        $upgradeRulesProperty->setValue($lowestLevel, $upgradeRules);
        
        // 预期将查询用户的升级进度
        $this->levelUpgradeProgressRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user]);
            
        // 执行升级方法
        $this->service->upgrade($this->user);
    }
    
    public function testUpgrade_withCurrentLevel_findsNextLevel(): void
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
            ->willReturn($userLevelRelation);
            
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
            
        // 设置 LevelRepository 行为 - 直接返回结果，避免 QueryBuilder 的复杂 mock
        $this->levelRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturnCallback(function() use ($nextLevel) {
                $mockBuilder = $this->getMockBuilder(QueryBuilder::class)
                    ->disableOriginalConstructor();
                
                $mockQuery = $this->getMockBuilder(Query::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                
                $mockQuery->method('getResult')->willReturn($nextLevel);
                
                $mockQueryBuilder = $mockBuilder->getMock();
                $mockQueryBuilder->method('where')->willReturnSelf();
                $mockQueryBuilder->method('setParameter')->willReturnSelf();
                $mockQueryBuilder->method('addOrderBy')->willReturnSelf();
                $mockQueryBuilder->method('getQuery')->willReturn($mockQuery);
                
                return $mockQueryBuilder;
            });
        
        // 预期将查询用户的升级进度
        $this->levelUpgradeProgressRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['user' => $this->user]);
            
        // 执行升级方法
        $this->service->upgrade($this->user);
    }
    
    public function testUpgrade_withNoNextLevel_doesNothing(): void
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
            ->willReturn($userLevelRelation);
            
        // 简化查询生成器逻辑，避免复杂的 mock
        // 设置 LevelRepository 行为 - 直接返回结果，避免 QueryBuilder 的复杂 mock
        $this->levelRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturnCallback(function() {
                $mockBuilder = $this->getMockBuilder(QueryBuilder::class)
                    ->disableOriginalConstructor();
                
                $mockQuery = $this->getMockBuilder(Query::class)
                    ->disableOriginalConstructor()
                    ->getMock();
                
                $mockQuery->method('getResult')->willReturn(null);
                
                $mockQueryBuilder = $mockBuilder->getMock();
                $mockQueryBuilder->method('where')->willReturnSelf();
                $mockQueryBuilder->method('setParameter')->willReturnSelf();
                $mockQueryBuilder->method('addOrderBy')->willReturnSelf();
                $mockQueryBuilder->method('getQuery')->willReturn($mockQuery);
                
                return $mockQueryBuilder;
            });
        
        // 预期不会查询升级进度
        $this->levelUpgradeProgressRepository
            ->expects($this->never())
            ->method('findBy');
            
        // 执行升级方法
        $this->service->upgrade($this->user);
    }
    
    public function testDegrade_isImplementedButEmpty(): void
    {
        // 测试降级方法是否存在但为空
        $this->expectNotToPerformAssertions();
        $this->service->degrade($this->user);
    }
}

/**
 * 测试用的UserInterface实现
 */
class ServiceTestUserInterface implements UserInterface
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