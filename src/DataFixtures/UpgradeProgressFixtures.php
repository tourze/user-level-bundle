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
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * 等级升级进度数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class UpgradeProgressFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
        // 获取或创建测试用户
        $user1 = $this->getOrCreateTestUser('upgrade-progress-test-user-1', '升级进度测试用户1');
        $user2 = $this->getOrCreateTestUser('upgrade-progress-test-user-2', '升级进度测试用户2');
        $user3 = $this->getOrCreateTestUser('upgrade-progress-test-user-3', '升级进度测试用户3');

        try {
            $pointsRule = $this->getReference(UpgradeRuleFixtures::POINTS_RULE_REFERENCE, UpgradeRule::class);
            $purchaseRule = $this->getReference(UpgradeRuleFixtures::PURCHASE_RULE_REFERENCE, UpgradeRule::class);
            $referralRule = $this->getReference(UpgradeRuleFixtures::REFERRAL_RULE_REFERENCE, UpgradeRule::class);
        } catch (\Exception $e) {
            // 如果升级规则引用不存在，创建测试规则（使用大数值避免冲突）
            $pointsRule = $this->createTestUpgradeRule($manager, 200001, '积分规则');
            $purchaseRule = $this->createTestUpgradeRule($manager, 200002, '购买规则');
            $referralRule = $this->createTestUpgradeRule($manager, 200003, '推荐规则');
        }

        $now = new \DateTimeImmutable();

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
            UpgradeRuleFixtures::class,
        ];
    }
}
