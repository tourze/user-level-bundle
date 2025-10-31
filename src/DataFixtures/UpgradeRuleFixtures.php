<?php

declare(strict_types=1);

namespace UserLevelBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * 等级升级规则数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class UpgradeRuleFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    // 常量定义引用名称
    public const POINTS_RULE_REFERENCE = 'points-rule';
    public const PURCHASE_RULE_REFERENCE = 'purchase-rule';
    public const REFERRAL_RULE_REFERENCE = 'referral-rule';
    public const VIP_RULE_REFERENCE = 'vip-rule';

    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        $now = CarbonImmutable::now();

        // 获取等级引用
        try {
            $silverLevel = $this->getReference(LevelFixtures::SILVER_LEVEL_REFERENCE, Level::class);
            $goldLevel = $this->getReference(LevelFixtures::GOLD_LEVEL_REFERENCE, Level::class);
            $platinumLevel = $this->getReference(LevelFixtures::PLATINUM_LEVEL_REFERENCE, Level::class);
            $diamondLevel = $this->getReference(LevelFixtures::DIAMOND_LEVEL_REFERENCE, Level::class);
        } catch (\Exception $e) {
            // 如果等级引用不存在，创建测试等级
            $silverLevel = $this->createTestLevel($manager, 2, '银牌会员');
            $goldLevel = $this->createTestLevel($manager, 3, '金牌会员');
            $platinumLevel = $this->createTestLevel($manager, 4, '铂金会员');
            $diamondLevel = $this->createTestLevel($manager, 5, '钻石会员');
        }

        // 创建积分升级规则（银牌等级）
        $pointsRule = new UpgradeRule();
        $pointsRule->setTitle('积分达到1000升级银牌');
        $pointsRule->setValue(1000);
        $pointsRule->setLevel($silverLevel);
        $pointsRule->setValid(true);
        $pointsRule->setCreatedBy('system');
        $pointsRule->setUpdatedBy('system');
        $pointsRule->setCreateTime($now->modify('-30 days'));
        $pointsRule->setUpdateTime($now->modify('-30 days'));

        $manager->persist($pointsRule);
        $this->addReference(self::POINTS_RULE_REFERENCE, $pointsRule);

        // 创建购买升级规则（金牌等级）
        $purchaseRule = new UpgradeRule();
        $purchaseRule->setTitle('购买5次升级金牌');
        $purchaseRule->setValue(5);
        $purchaseRule->setLevel($goldLevel);
        $purchaseRule->setValid(true);
        $purchaseRule->setCreatedBy('system');
        $purchaseRule->setUpdatedBy('system');
        $purchaseRule->setCreateTime($now->modify('-30 days'));
        $purchaseRule->setUpdateTime($now->modify('-30 days'));

        $manager->persist($purchaseRule);
        $this->addReference(self::PURCHASE_RULE_REFERENCE, $purchaseRule);

        // 创建推荐升级规则（铂金等级）
        $referralRule = new UpgradeRule();
        $referralRule->setTitle('推荐3人升级铂金');
        $referralRule->setValue(3);
        $referralRule->setLevel($platinumLevel);
        $referralRule->setValid(true);
        $referralRule->setCreatedBy('system');
        $referralRule->setUpdatedBy('system');
        $referralRule->setCreateTime($now->modify('-30 days'));
        $referralRule->setUpdateTime($now->modify('-30 days'));

        $manager->persist($referralRule);
        $this->addReference(self::REFERRAL_RULE_REFERENCE, $referralRule);

        // 创建VIP升级规则（钻石等级）
        $vipRule = new UpgradeRule();
        $vipRule->setTitle('VIP会员自动升级钻石');
        $vipRule->setValue(1);
        $vipRule->setLevel($diamondLevel);
        $vipRule->setValid(true);
        $vipRule->setCreatedBy('system');
        $vipRule->setUpdatedBy('system');
        $vipRule->setCreateTime($now->modify('-30 days'));
        $vipRule->setUpdateTime($now->modify('-30 days'));

        $manager->persist($vipRule);
        $this->addReference(self::VIP_RULE_REFERENCE, $vipRule);

        $manager->flush();
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
            LevelFixtures::class,
        ];
    }
}
