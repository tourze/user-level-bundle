<?php

namespace UserLevelBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * @internal
 */
#[CoversClass(Level::class)]
final class LevelTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Level();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'title' => ['title', 'test_value'],
            'level' => ['level', 123],
            'valid' => ['valid', true],
        ];
    }

    public function testGetIdWhenNewInstanceReturnsNull(): void
    {
        $level = $this->createMock(Level::class);
        $level->method('getId')->willReturn(null);
        $this->assertNull($level->getId());
    }

    public function testSetTitleWithValidTitleStoresTitle(): void
    {
        $level = $this->createMock(Level::class);
        $title = 'VIP会员';

        $level->method('getTitle')->willReturn($title);
        $level->setTitle($title);

        $this->assertSame($title, $level->getTitle());
    }

    public function testSetLevelWithIntegerStoresLevel(): void
    {
        $level = $this->createMock(Level::class);
        $levelValue = 3;

        $level->method('getLevel')->willReturn($levelValue);
        $level->setLevel($levelValue);

        $this->assertSame($levelValue, $level->getLevel());
    }

    public function testIsValidWithDefaultValueReturnsFalse(): void
    {
        $level = $this->createMock(Level::class);
        $level->method('isValid')->willReturn(false);
        $this->assertFalse($level->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $level = $this->createMock(Level::class);
        $level->method('isValid')->willReturn(true);
        $level->setValid(true);

        $this->assertTrue($level->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $level = $this->createMock(Level::class);
        $level->method('isValid')->willReturn(false);
        $level->setValid(false);

        $this->assertFalse($level->isValid());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $level = $this->createMock(Level::class);
        $createdBy = 'admin';

        $level->method('getCreatedBy')->willReturn($createdBy);
        $level->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $level->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $level = $this->createMock(Level::class);
        $updatedBy = 'admin';

        $level->method('getUpdatedBy')->willReturn($updatedBy);
        $level->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $level->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $level = $this->createMock(Level::class);
        $datetime = new \DateTimeImmutable();

        $level->method('getCreateTime')->willReturn($datetime);
        $level->setCreateTime($datetime);

        $this->assertSame($datetime, $level->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $level = $this->createMock(Level::class);
        $datetime = new \DateTimeImmutable();

        $level->method('getUpdateTime')->willReturn($datetime);
        $level->setUpdateTime($datetime);

        $this->assertSame($datetime, $level->getUpdateTime());
    }

    public function testAddUpgradeRuleWithNewRuleAddsRuleToCollection(): void
    {
        $level = $this->createMock(Level::class);
        $upgradeRule = $this->createMock(UpgradeRule::class);

        $level->method('getUpgradeRules')->willReturn(new ArrayCollection([$upgradeRule]));
        $level->addUpgradeRule($upgradeRule);

        $this->assertCount(1, $level->getUpgradeRules());
        $this->assertTrue($level->getUpgradeRules()->contains($upgradeRule));
    }

    public function testAddUpgradeRuleWithDuplicateRuleDoesNotAddAgain(): void
    {
        $level = $this->createMock(Level::class);
        $upgradeRule = $this->createMock(UpgradeRule::class);

        $level->method('getUpgradeRules')->willReturn(new ArrayCollection([$upgradeRule]));
        $level->addUpgradeRule($upgradeRule);
        $level->addUpgradeRule($upgradeRule);

        $this->assertCount(1, $level->getUpgradeRules());
    }

    public function testRemoveUpgradeRuleWithExistingRuleRemovesRuleFromCollection(): void
    {
        $level = $this->createMock(Level::class);
        $upgradeRule = $this->createMock(UpgradeRule::class);

        $level->method('getUpgradeRules')->willReturn(new ArrayCollection());
        $level->addUpgradeRule($upgradeRule);
        $level->removeUpgradeRule($upgradeRule);

        $this->assertCount(0, $level->getUpgradeRules());
        $this->assertFalse($level->getUpgradeRules()->contains($upgradeRule));
    }

    public function testRemoveUpgradeRuleWithNonExistingRuleDoesNothing(): void
    {
        $level = $this->createMock(Level::class);
        $upgradeRule = $this->createMock(UpgradeRule::class);

        $level->method('getUpgradeRules')->willReturn(new ArrayCollection());
        $level->removeUpgradeRule($upgradeRule);

        $this->assertCount(0, $level->getUpgradeRules());
    }

    public function testRetrieveAdminArrayReturnsExpectedArray(): void
    {
        $level = $this->createMock(Level::class);
        $level->method('getTitle')->willReturn('VIP会员');
        $level->method('getLevel')->willReturn(3);
        $level->method('getId')->willReturn(null);

        $expectedArray = [
            'level' => 3,
            'title' => 'VIP会员',
            'id' => null,
        ];

        $level->method('retrieveAdminArray')->willReturn($expectedArray);
        $result = $level->retrieveAdminArray();

        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(3, $result['level']);
        $this->assertEquals('VIP会员', $result['title']);
        $this->assertNull($result['id']);
    }
}
