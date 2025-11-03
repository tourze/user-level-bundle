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
        $level = new Level();
        $this->assertNull($level->getId());
    }

    public function testSetTitleWithValidTitleStoresTitle(): void
    {
        $level = new Level();
        $title = 'VIP会员';
        $level->setTitle($title);
        $this->assertSame($title, $level->getTitle());
    }

    public function testSetLevelWithIntegerStoresLevel(): void
    {
        $level = new Level();
        $levelValue = 3;
        $level->setLevel($levelValue);
        $this->assertSame($levelValue, $level->getLevel());
    }

    public function testIsValidWithDefaultValueReturnsFalse(): void
    {
        $level = new Level();
        $this->assertFalse($level->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $level = new Level();
        $level->setValid(true);
        $this->assertTrue($level->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $level = new Level();
        $level->setValid(false);
        $this->assertFalse($level->isValid());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $level = new Level();
        $createdBy = 'admin';
        $level->setCreatedBy($createdBy);
        $this->assertSame($createdBy, $level->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $level = new Level();
        $updatedBy = 'admin';
        $level->setUpdatedBy($updatedBy);
        $this->assertSame($updatedBy, $level->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $level = new Level();
        $datetime = new \DateTimeImmutable();
        $level->setCreateTime($datetime);
        $this->assertSame($datetime, $level->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $level = new Level();
        $datetime = new \DateTimeImmutable();
        $level->setUpdateTime($datetime);
        $this->assertSame($datetime, $level->getUpdateTime());
    }

    public function testAddUpgradeRuleWithNewRuleAddsRuleToCollection(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();

        $level->addUpgradeRule($upgradeRule);

        $this->assertCount(1, $level->getUpgradeRules());
        $this->assertTrue($level->getUpgradeRules()->contains($upgradeRule));
    }

    public function testAddUpgradeRuleWithDuplicateRuleDoesNotAddAgain(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();

        $level->addUpgradeRule($upgradeRule);
        $level->addUpgradeRule($upgradeRule);

        $this->assertCount(1, $level->getUpgradeRules());
    }

    public function testRemoveUpgradeRuleWithExistingRuleRemovesRuleFromCollection(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();

        $level->addUpgradeRule($upgradeRule);
        $level->removeUpgradeRule($upgradeRule);

        $this->assertCount(0, $level->getUpgradeRules());
        $this->assertFalse($level->getUpgradeRules()->contains($upgradeRule));
    }

    public function testRemoveUpgradeRuleWithNonExistingRuleDoesNothing(): void
    {
        $level = new Level();
        $upgradeRule = new UpgradeRule();

        $level->removeUpgradeRule($upgradeRule);

        $this->assertCount(0, $level->getUpgradeRules());
    }

    public function testRetrieveAdminArrayReturnsExpectedArray(): void
    {
        $level = new Level();
        $level->setTitle('VIP会员');
        $level->setLevel(3);

        $result = $level->retrieveAdminArray();

        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(3, $result['level']);
        $this->assertEquals('VIP会员', $result['title']);
        $this->assertNull($result['id']);
    }
}
