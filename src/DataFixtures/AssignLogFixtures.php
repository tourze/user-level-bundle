<?php

declare(strict_types=1);

namespace UserLevelBundle\DataFixtures;

use BizUserBundle\DataFixtures\BizUserFixtures;
use BizUserBundle\Entity\BizUser;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserServiceConstants;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Entity\Level;

/**
 * 等级分配记录数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class AssignLogFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('zh_CN');

        // 获取用户和等级引用
        try {
            $user1 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 1, BizUser::class);
            $user2 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 2, BizUser::class);
        } catch (\Exception $e) {
            // 如果用户引用不存在，创建测试用户
            $user1 = $this->createTestUser($manager, 'test1@user-level-test.local');
            $user2 = $this->createTestUser($manager, 'test2@user-level-test.local');
        }

        try {
            $bronzeLevel = $this->getReference(LevelFixtures::BRONZE_LEVEL_REFERENCE, Level::class);
            $silverLevel = $this->getReference(LevelFixtures::SILVER_LEVEL_REFERENCE, Level::class);
            $goldLevel = $this->getReference(LevelFixtures::GOLD_LEVEL_REFERENCE, Level::class);
        } catch (\Exception $e) {
            // 如果等级引用不存在，创建测试等级
            $bronzeLevel = $this->createTestLevel($manager, 1, 'Bronze Level');
            $silverLevel = $this->createTestLevel($manager, 2, 'Silver Level');
            $goldLevel = $this->createTestLevel($manager, 3, 'Gold Level');
        }

        // 创建用户1从铜牌升级到银牌的记录
        $assignLog1 = new AssignLog();
        $assignLog1->setUser($user1);
        $assignLog1->setOldLevel($bronzeLevel);
        $assignLog1->setNewLevel($silverLevel);
        $assignLog1->setType(1); // 升级
        $assignLog1->setAssignTime(CarbonImmutable::now()->modify('-10 days'));
        $assignLog1->setRemark('积分达到升级要求');
        $assignLog1->setCreatedBy('system');
        $assignLog1->setUpdatedBy('system');
        $assignLog1->setCreateTime(CarbonImmutable::now()->modify('-10 days'));
        $assignLog1->setUpdateTime(CarbonImmutable::now()->modify('-10 days'));

        $manager->persist($assignLog1);

        // 创建用户2从银牌升级到金牌的记录
        $assignLog2 = new AssignLog();
        $assignLog2->setUser($user2);
        $assignLog2->setOldLevel($silverLevel);
        $assignLog2->setNewLevel($goldLevel);
        $assignLog2->setType(1); // 升级
        $assignLog2->setAssignTime(CarbonImmutable::now()->modify('-5 days'));
        $assignLog2->setRemark('VIP购买升级');
        $assignLog2->setCreatedBy('admin');
        $assignLog2->setUpdatedBy('admin');
        $assignLog2->setCreateTime(CarbonImmutable::now()->modify('-5 days'));
        $assignLog2->setUpdateTime(CarbonImmutable::now()->modify('-5 days'));

        $manager->persist($assignLog2);

        // 创建一个降级记录
        $assignLog3 = new AssignLog();
        $assignLog3->setUser($user1);
        $assignLog3->setOldLevel($silverLevel);
        $assignLog3->setNewLevel($bronzeLevel);
        $assignLog3->setType(0); // 降级
        $assignLog3->setAssignTime(CarbonImmutable::now()->modify('-2 days'));
        $assignLog3->setRemark('违规行为导致降级');
        $assignLog3->setCreatedBy('moderator');
        $assignLog3->setUpdatedBy('moderator');
        $assignLog3->setCreateTime(CarbonImmutable::now()->modify('-2 days'));
        $assignLog3->setUpdateTime(CarbonImmutable::now()->modify('-2 days'));

        $manager->persist($assignLog3);

        $manager->flush();
    }

    /**
     * 创建测试用户
     */
    private function createTestUser(ObjectManager $manager, string $email): UserInterface
    {
        // 创建一个简单的用户对象用于测试
        $user = new BizUser();
        $user->setEmail($email);
        $user->setUsername('test_user_' . uniqid());
        $user->setPasswordHash('password123');
        $user->setNickName('Test User');
        $manager->persist($user);

        return $user;
    }

    /**
     * 创建测试等级
     */
    private function createTestLevel(ObjectManager $manager, int $level, string $title): Level
    {
        $testLevel = new Level();
        $testLevel->setLevel($level);
        $testLevel->setTitle($title . '_test_' . uniqid()); // 添加唯一后缀避免标题冲突
        $testLevel->setValid(true);
        $testLevel->setCreatedBy('system');
        $testLevel->setUpdatedBy('system');
        $testLevel->setCreateTime(new \DateTimeImmutable());
        $testLevel->setUpdateTime(new \DateTimeImmutable());
        $manager->persist($testLevel);

        return $testLevel;
    }

    public function getDependencies(): array
    {
        return [
            BizUserFixtures::class,
            LevelFixtures::class,
        ];
    }
}
