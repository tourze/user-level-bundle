<?php

declare(strict_types=1);

namespace UserLevelBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\UserServiceContracts\UserManagerInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;

/**
 * 用户等级关系数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class UserLevelRelationFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();

        // 获取等级引用
        try {
            $bronzeLevel = $this->getReference(LevelFixtures::BRONZE_LEVEL_REFERENCE, Level::class);
            $silverLevel = $this->getReference(LevelFixtures::SILVER_LEVEL_REFERENCE, Level::class);
            $goldLevel = $this->getReference(LevelFixtures::GOLD_LEVEL_REFERENCE, Level::class);
            $platinumLevel = $this->getReference(LevelFixtures::PLATINUM_LEVEL_REFERENCE, Level::class);
            $diamondLevel = $this->getReference(LevelFixtures::DIAMOND_LEVEL_REFERENCE, Level::class);
        } catch (\Exception $e) {
            // 如果等级引用不存在，创建测试等级（使用大数值避免与 LevelFixtures 冲突）
            $bronzeLevel = $this->createTestLevel($manager, 400001, '铜牌会员');
            $silverLevel = $this->createTestLevel($manager, 400002, '银牌会员');
            $goldLevel = $this->createTestLevel($manager, 400003, '金牌会员');
            $platinumLevel = $this->createTestLevel($manager, 400004, '铂金会员');
            $diamondLevel = $this->createTestLevel($manager, 400005, '钻石会员');
        }

        // 获取或创建测试用户
        $users = [];
        for ($i = 1; $i <= 5; ++$i) {
            $user = $this->getOrCreateTestUser("user-level-relation-test-user-{$i}", "等级关系测试用户{$i}");
            $users[] = $user;
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
     * 获取或创建测试用户
     */
    private function getOrCreateTestUser(string $userIdentifier, string $nickName): UserInterface
    {
        // 尝试加载已存在的用户
        $user = $this->userManager->loadUserByIdentifier($userIdentifier);

        // 如果用户不存在，创建一个新的测试用户
        if (null === $user) {
            $user = $this->userManager->createUser($userIdentifier, $nickName);
            $this->userManager->saveUser($user);
        }

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
            LevelFixtures::class,
        ];
    }
}
