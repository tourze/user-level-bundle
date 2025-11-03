<?php

namespace UserLevelBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * @internal
 */
#[CoversClass(UpgradeRule::class)]
final class UpgradeRuleTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UpgradeRule();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'title' => ['title', 'test_value'],
            'value' => ['value', 123],
            'valid' => ['valid', true],
        ];
    }

    public function testGetIdWhenNewInstanceReturnsNull(): void
    {
        $rule = new UpgradeRule();
        $this->assertNull($rule->getId());
    }

    public function testSetTitleWithValidTitleStoresTitle(): void
    {
        $rule = new UpgradeRule();
        $title = '消费金额';

        $rule->setTitle($title);

        $this->assertSame($title, $rule->getTitle());
    }

    public function testSetValueWithIntegerStoresValue(): void
    {
        $rule = new UpgradeRule();
        $value = 10000;

        $rule->setValue($value);

        $this->assertSame($value, $rule->getValue());
    }

    public function testSetValueWithNullStoresNull(): void
    {
        $rule = new UpgradeRule();
        $rule->setValue(0);

        $this->assertEquals(0, $rule->getValue());
    }

    public function testSetLevelWithLevelObjectStoresLevel(): void
    {
        $rule = new UpgradeRule();
        $level = new Level();
        $level->setLevel(1);
        $level->setTitle('VIP1');
        $level->setValid(true);

        $rule->setLevel($level);

        $this->assertSame($level, $rule->getLevel());
    }

    public function testIsValidWithDefaultValueReturnsFalse(): void
    {
        $rule = new UpgradeRule();
        $this->assertFalse($rule->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $rule = new UpgradeRule();
        $rule->setValid(true);

        $this->assertTrue($rule->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $rule = new UpgradeRule();
        $rule->setValid(false);

        $this->assertFalse($rule->isValid());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $rule = new UpgradeRule();
        $createdBy = 'admin';

        $rule->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $rule->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $rule = new UpgradeRule();
        $updatedBy = 'admin';

        $rule->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $rule->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $rule = new UpgradeRule();
        $datetime = new \DateTimeImmutable();

        $rule->setCreateTime($datetime);

        $this->assertSame($datetime, $rule->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $rule = new UpgradeRule();
        $datetime = new \DateTimeImmutable();

        $rule->setUpdateTime($datetime);

        $this->assertSame($datetime, $rule->getUpdateTime());
    }

    public function testSetCreatedByWithNullStoresNull(): void
    {
        $rule = new UpgradeRule();
        $rule->setCreatedBy(null);

        $this->assertNull($rule->getCreatedBy());
    }

    public function testSetUpdatedByWithNullStoresNull(): void
    {
        $rule = new UpgradeRule();
        $rule->setUpdatedBy(null);

        $this->assertNull($rule->getUpdatedBy());
    }
}
