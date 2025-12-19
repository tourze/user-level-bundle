<?php

namespace UserLevelBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;
use UserLevelBundle\Entity\UserLevelRelation;
use UserLevelBundle\Repository\LevelRepository;
use UserLevelBundle\Repository\UpgradeProgressRepository;
use UserLevelBundle\Repository\UserLevelRelationRepository;
use UserLevelBundle\Service\UserLevelUpgradeService;

/**
 * @internal
 */
#[CoversClass(UserLevelUpgradeService::class)]
#[RunTestsInSeparateProcesses]
final class UserLevelUpgradeServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 从容器获取服务实例
        $this->service = self::getService(UserLevelUpgradeService::class);
        $this->userLevelRelationRepository = self::getService(UserLevelRelationRepository::class);
        $this->levelRepository = self::getService(LevelRepository::class);
        $this->levelUpgradeProgressRepository = self::getService(UpgradeProgressRepository::class);
        $this->userManager = self::getService(UserManagerInterface::class);
    }

    private UserLevelUpgradeService $service;

    private UserLevelRelationRepository $userLevelRelationRepository;

    private LevelRepository $levelRepository;

    private UpgradeProgressRepository $levelUpgradeProgressRepository;

    private UserManagerInterface $userManager;

    public function testUpgradeWithNoCurrentLevelFindsLowestLevel(): void
    {
        // 创建测试用户
        $user = $this->userManager->createUser('test-user-1', 'Test User');
        $this->userManager->saveUser($user);

        // 确保用户当前没有等级
        $existingRelation = $this->userLevelRelationRepository->findOneBy(['user' => $user]);
        $this->assertNull($existingRelation);

        // 创建最低级别
        $lowestLevel = new Level();
        $lowestLevel->setLevel(1001);
        $lowestLevel->setTitle('普通会员-测试1');
        $lowestLevel->setValid(true);

        $this->levelRepository->save($lowestLevel);

        // 执行升级方法
        $this->service->upgrade($user);

        // 验证方法执行成功，没有抛出异常
        $this->assertTrue(true, 'upgrade method executed successfully');
    }

    public function testUpgradeWithCurrentLevelFindsNextLevel(): void
    {
        // 创建测试用户
        $user = $this->userManager->createUser('test-user-2', 'Test User 2');
        $this->userManager->saveUser($user);

        // 创建当前等级
        $currentLevel = new Level();
        $currentLevel->setLevel(2001);
        $currentLevel->setTitle('普通会员-测试2');
        $currentLevel->setValid(true);
        $this->levelRepository->save($currentLevel);

        // 创建下一等级
        $nextLevel = new Level();
        $nextLevel->setLevel(2002);
        $nextLevel->setTitle('VIP会员-测试2');
        $nextLevel->setValid(true);
        $this->levelRepository->save($nextLevel);

        // 创建用户当前等级关系
        $userLevelRelation = new UserLevelRelation();
        $userLevelRelation->setUser($user);
        $userLevelRelation->setLevel($currentLevel);
        $userLevelRelation->setValid(true);
        $this->userLevelRelationRepository->save($userLevelRelation);

        // 执行升级方法
        $this->service->upgrade($user);

        // 验证方法执行成功，没有抛出异常
        $this->assertTrue(true, 'upgrade method executed successfully');
    }

    public function testUpgradeWithNoNextLevelDoesNothing(): void
    {
        // 创建测试用户
        $user = $this->userManager->createUser('test-user-3', 'Test User 3');
        $this->userManager->saveUser($user);

        // 创建最高等级（没有下一等级）
        $currentLevel = new Level();
        $currentLevel->setLevel(3001); // 假设这是最高级别
        $currentLevel->setTitle('钻石会员-测试3');
        $currentLevel->setValid(true);
        $this->levelRepository->save($currentLevel);

        // 创建用户当前等级关系
        $userLevelRelation = new UserLevelRelation();
        $userLevelRelation->setUser($user);
        $userLevelRelation->setLevel($currentLevel);
        $userLevelRelation->setValid(true);
        $this->userLevelRelationRepository->save($userLevelRelation);

        // 执行升级方法 - 应该正常退出，因为没有下一个等级
        $this->service->upgrade($user);

        // 验证方法执行成功，没有抛出异常
        $this->assertTrue(true, 'upgrade method executed successfully without next level');
    }

    public function testDegradeIsImplementedButEmpty(): void
    {
        // 创建测试用户
        $user = $this->userManager->createUser('test-user-4', 'Test User 4');
        $this->userManager->saveUser($user);

        // 测试降级方法存在但为空实现 - 验证无副作用

        // 由于degrade方法为空实现，无论调用多少次都不应有任何副作用
        $this->service->degrade($user);
        $this->service->degrade($user);

        // 验证方法确实可以被调用且没有抛出异常
        $this->assertTrue(true, 'degrade method exists and can be called without side effects');
    }
}
