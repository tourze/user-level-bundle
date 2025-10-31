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
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('getId')->willReturn(null);
        $this->assertNull($rule->getId());
    }

    public function testSetTitleWithValidTitleStoresTitle(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $title = '消费金额';

        $rule->method('getTitle')->willReturn($title);
        $rule->setTitle($title);

        $this->assertSame($title, $rule->getTitle());
    }

    public function testSetValueWithIntegerStoresValue(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $value = 10000;

        $rule->method('getValue')->willReturn($value);
        $rule->setValue($value);

        $this->assertSame($value, $rule->getValue());
    }

    public function testSetValueWithNullStoresNull(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('getValue')->willReturn(0);
        $rule->setValue(0);

        $this->assertEquals(0, $rule->getValue());
    }

    public function testSetLevelWithLevelObjectStoresLevel(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $level = $this->createMock(Level::class);

        $rule->method('getLevel')->willReturn($level);
        $rule->setLevel($level);

        $this->assertSame($level, $rule->getLevel());
    }

    public function testIsValidWithDefaultValueReturnsFalse(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('isValid')->willReturn(false);
        $this->assertFalse($rule->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('isValid')->willReturn(true);
        $rule->setValid(true);

        $this->assertTrue($rule->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('isValid')->willReturn(false);
        $rule->setValid(false);

        $this->assertFalse($rule->isValid());
    }

    public function testSetCreatedByWithValidStringStoresCreatedBy(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $createdBy = 'admin';

        $rule->method('getCreatedBy')->willReturn($createdBy);
        $rule->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $rule->getCreatedBy());
    }

    public function testSetUpdatedByWithValidStringStoresUpdatedBy(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $updatedBy = 'admin';

        $rule->method('getUpdatedBy')->willReturn($updatedBy);
        $rule->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $rule->getUpdatedBy());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $datetime = new \DateTimeImmutable();

        $rule->method('getCreateTime')->willReturn($datetime);
        $rule->setCreateTime($datetime);

        $this->assertSame($datetime, $rule->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $datetime = new \DateTimeImmutable();

        $rule->method('getUpdateTime')->willReturn($datetime);
        $rule->setUpdateTime($datetime);

        $this->assertSame($datetime, $rule->getUpdateTime());
    }

    public function testSetCreatedByWithNullStoresNull(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('getCreatedBy')->willReturn(null);
        $rule->setCreatedBy(null);

        $this->assertNull($rule->getCreatedBy());
    }

    public function testSetUpdatedByWithNullStoresNull(): void
    {
        $rule = $this->createMock(UpgradeRule::class);
        $rule->method('getUpdatedBy')->willReturn(null);
        $rule->setUpdatedBy(null);

        $this->assertNull($rule->getUpdatedBy());
    }
}
