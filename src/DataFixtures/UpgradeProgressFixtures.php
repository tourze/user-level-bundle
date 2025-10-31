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
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * 等级升级进度数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class UpgradeProgressFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        // 获取用户和升级规则引用
        try {
            $user1 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 1, BizUser::class);
            $user2 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 2, BizUser::class);
            $user3 = $this->getReference(UserServiceConstants::NORMAL_USER_REFERENCE_PREFIX . 3, BizUser::class);
        } catch (\Exception $e) {
            // 如果用户引用不存在，创建测试用户
            $user1 = $this->createTestUser($manager, 'test1@user-level-test.local');
            $user2 = $this->createTestUser($manager, 'test2@user-level-test.local');
            $user3 = $this->createTestUser($manager, 'test3@user-level-test.local');
        }

        try {
            $pointsRule = $this->getReference(UpgradeRuleFixtures::POINTS_RULE_REFERENCE, UpgradeRule::class);
            $purchaseRule = $this->getReference(UpgradeRuleFixtures::PURCHASE_RULE_REFERENCE, UpgradeRule::class);
            $referralRule = $this->getReference(UpgradeRuleFixtures::REFERRAL_RULE_REFERENCE, UpgradeRule::class);
        } catch (\Exception $e) {
            // 如果升级规则引用不存在，创建测试规则
            $pointsRule = $this->createTestUpgradeRule($manager, 1, '积分规则');
            $purchaseRule = $this->createTestUpgradeRule($manager, 2, '购买规则');
            $referralRule = $this->createTestUpgradeRule($manager, 3, '推荐规则');
        }

        $now = CarbonImmutable::now();

        // 用户1的积分进度
        $progress1 = new UpgradeProgress();
        $progress1->setUser($user1);
        $progress1->setUpgradeRule($pointsRule);
        $progress1->setValue(750); // 进度 75%
        $progress1->setCreateTime($now->modify('-5 days'));
        $progress1->setUpdateTime($now->modify('-1 day'));

        $manager->persist($progress1);

        // 用户2的购买进度
        $progress2 = new UpgradeProgress();
        $progress2->setUser($user2);
        $progress2->setUpgradeRule($purchaseRule);
        $progress2->setValue(3); // 进度 60%
        $progress2->setCreateTime($now->modify('-7 days'));
        $progress2->setUpdateTime($now->modify('-2 days'));

        $manager->persist($progress2);

        // 用户3的推荐进度
        $progress3 = new UpgradeProgress();
        $progress3->setUser($user3);
        $progress3->setUpgradeRule($referralRule);
        $progress3->setValue(1); // 进度 33%
        $progress3->setCreateTime($now->modify('-3 days'));
        $progress3->setUpdateTime($now->modify('-3 days'));

        $manager->persist($progress3);

        $manager->flush();
    }

    /**
     * 创建测试用户
     */
    private function createTestUser(ObjectManager $manager, string $email): UserInterface
    {
        $user = new BizUser();
        $user->setEmail($email);
        $user->setUsername('test_user_' . uniqid());
        $user->setPasswordHash('password123');
        $user->setNickName('Test User');
        $manager->persist($user);

        return $user;
    }

    /**
     * 创建测试升级规则
     */
    private function createTestUpgradeRule(ObjectManager $manager, int $level, string $title): UpgradeRule
    {
        $rule = new UpgradeRule();
        $rule->setLevel($this->createTestLevel($manager, $level, "Level {$level}"));
        $rule->setTitle($title);
        $rule->setValue(1000);
        $rule->setValid(true);
        $rule->setCreatedBy('system');
        $rule->setUpdatedBy('system');
        $rule->setCreateTime(new \DateTimeImmutable());
        $rule->setUpdateTime(new \DateTimeImmutable());
        $manager->persist($rule);

        return $rule;
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
            UpgradeRuleFixtures::class,
        ];
    }
}
