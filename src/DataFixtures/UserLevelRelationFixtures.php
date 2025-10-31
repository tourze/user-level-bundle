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
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserServiceConstants;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;

/**
 * 用户等级关系数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class UserLevelRelationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        $now = CarbonImmutable::now();

        // 获取等级引用
        try {
            $bronzeLevel = $this->getReference(LevelFixtures::BRONZE_LEVEL_REFERENCE, Level::class);
            $silverLevel = $this->getReference(LevelFixtures::SILVER_LEVEL_REFERENCE, Level::class);
            $goldLevel = $this->getReference(LevelFixtures::GOLD_LEVEL_REFERENCE, Level::class);
            $platinumLevel = $this->getReference(LevelFixtures::PLATINUM_LEVEL_REFERENCE, Level::class);
            $diamondLevel = $this->getReference(LevelFixtures::DIAMOND_LEVEL_REFERENCE, Level::class);
        } catch (\Exception $e) {
            // 如果等级引用不存在，创建测试等级
            $bronzeLevel = $this->createTestLevel($manager, 1, '铜牌会员');
            $silverLevel = $this->createTestLevel($manager, 2, '银牌会员');
            $goldLevel = $this->createTestLevel($manager, 3, '金牌会员');
            $platinumLevel = $this->createTestLevel($manager, 4, '铂金会员');
            $diamondLevel = $this->createTestLevel($manager, 5, '钻石会员');
        }

        // 尝试获取外部用户引用
        try {
            $user1 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 1, BizUser::class);
            $user2 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 2, BizUser::class);
            $user3 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 3, BizUser::class);
            $user4 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 4, BizUser::class);
            $user5 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 5, BizUser::class);

            $users = [$user1, $user2, $user3, $user4, $user5];
        } catch (\Exception $e) {
            // 如果用户引用不存在，创建测试用户以确保数据一致性
            $users = [];
            for ($i = 1; $i <= 5; ++$i) {
                $user = $this->createTestUser($manager, "test-user-{$i}@user-level-test.local", "Test User {$i}");
                $users[] = $user;
            }
        }

        // 创建用户等级关系
        $levels = [$bronzeLevel, $silverLevel, $goldLevel, $platinumLevel, $diamondLevel];
        $days = [20, 15, 12, 8, 5];

        foreach ($users as $index => $user) {
            $relation = new UserLevelRelation();
            $relation->setUser($user);
            $relation->setLevel($levels[$index]);
            $relation->setValid(true);
            $relation->setCreateTime($now->modify("-{$days[$index]} days"));
            $relation->setUpdateTime($now->modify('-' . ($days[$index] - 10) . ' days'));

            $manager->persist($relation);
        }

        $manager->flush();
    }

    /**
     * 创建测试用户
     */
    private function createTestUser(ObjectManager $manager, string $email, string $nickName): UserInterface
    {
        $user = new BizUser();
        $user->setUsername('test_user_' . uniqid());
        $user->setEmail($email);
        $user->setNickName($nickName);
        $user->setPasswordHash('test_password_hash');
        $user->setValid(true);
        $user->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $user->setUpdateTime(CarbonImmutable::now()->modify('-30 days'));

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
        $testLevel->setCreateTime(CarbonImmutable::now()->modify('-30 days'));
        $testLevel->setUpdateTime(CarbonImmutable::now()->modify('-30 days'));

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
