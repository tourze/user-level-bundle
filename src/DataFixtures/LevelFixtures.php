<?php

declare(strict_types=1);

namespace UserLevelBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use UserLevelBundle\Entity\Level;

/**
 * 用户等级数据填充
 */
#[When(env: 'test')]
#[When(env: 'dev')]
class LevelFixtures extends Fixture implements FixtureGroupInterface
{
    // 常量定义引用名称
    public const BRONZE_LEVEL_REFERENCE = 'bronze-level';
    public const SILVER_LEVEL_REFERENCE = 'silver-level';
    public const GOLD_LEVEL_REFERENCE = 'gold-level';
    public const PLATINUM_LEVEL_REFERENCE = 'platinum-level';
    public const DIAMOND_LEVEL_REFERENCE = 'diamond-level';

    public static function getGroups(): array
    {
        return ['user-level'];
    }

    public function load(ObjectManager $manager): void
    {
        $now = CarbonImmutable::now();

        // 创建铜牌等级
        $bronzeLevel = new Level();
        $bronzeLevel->setTitle('铜牌会员');
        $bronzeLevel->setLevel(1);
        $bronzeLevel->setValid(true);
        $bronzeLevel->setCreatedBy('system');
        $bronzeLevel->setUpdatedBy('system');
        $bronzeLevel->setCreateTime($now->modify('-30 days'));
        $bronzeLevel->setUpdateTime($now->modify('-30 days'));

        $manager->persist($bronzeLevel);
        $this->addReference(self::BRONZE_LEVEL_REFERENCE, $bronzeLevel);

        // 创建银牌等级
        $silverLevel = new Level();
        $silverLevel->setTitle('银牌会员');
        $silverLevel->setLevel(2);
        $silverLevel->setValid(true);
        $silverLevel->setCreatedBy('system');
        $silverLevel->setUpdatedBy('system');
        $silverLevel->setCreateTime($now->modify('-30 days'));
        $silverLevel->setUpdateTime($now->modify('-30 days'));

        $manager->persist($silverLevel);
        $this->addReference(self::SILVER_LEVEL_REFERENCE, $silverLevel);

        // 创建金牌等级
        $goldLevel = new Level();
        $goldLevel->setTitle('金牌会员');
        $goldLevel->setLevel(3);
        $goldLevel->setValid(true);
        $goldLevel->setCreatedBy('system');
        $goldLevel->setUpdatedBy('system');
        $goldLevel->setCreateTime($now->modify('-30 days'));
        $goldLevel->setUpdateTime($now->modify('-30 days'));

        $manager->persist($goldLevel);
        $this->addReference(self::GOLD_LEVEL_REFERENCE, $goldLevel);

        // 创建铂金等级
        $platinumLevel = new Level();
        $platinumLevel->setTitle('铂金会员');
        $platinumLevel->setLevel(4);
        $platinumLevel->setValid(true);
        $platinumLevel->setCreatedBy('system');
        $platinumLevel->setUpdatedBy('system');
        $platinumLevel->setCreateTime($now->modify('-30 days'));
        $platinumLevel->setUpdateTime($now->modify('-30 days'));

        $manager->persist($platinumLevel);
        $this->addReference(self::PLATINUM_LEVEL_REFERENCE, $platinumLevel);

        // 创建钻石等级
        $diamondLevel = new Level();
        $diamondLevel->setTitle('钻石会员');
        $diamondLevel->setLevel(5);
        $diamondLevel->setValid(true);
        $diamondLevel->setCreatedBy('system');
        $diamondLevel->setUpdatedBy('system');
        $diamondLevel->setCreateTime($now->modify('-30 days'));
        $diamondLevel->setUpdateTime($now->modify('-30 days'));

        $manager->persist($diamondLevel);
        $this->addReference(self::DIAMOND_LEVEL_REFERENCE, $diamondLevel);

        $manager->flush();
    }
}
