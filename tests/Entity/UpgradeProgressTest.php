<?php

namespace UserLevelBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserLevelBundle\Entity\UpgradeProgress;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * @internal
 */
#[CoversClass(UpgradeProgress::class)]
final class UpgradeProgressTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UpgradeProgress();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'value' => ['value', 100],
        ];
    }

    public function testGetIdWhenNewInstanceReturnsNull(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $progress->method('getId')->willReturn(null);
        $this->assertNull($progress->getId());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $user = $this->createMock(UserInterface::class);

        $progress->method('getUser')->willReturn($user);
        $progress->setUser($user);

        $this->assertSame($user, $progress->getUser());
    }

    public function testSetUpgradeRuleWithRuleObjectStoresRule(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $rule = $this->createMock(UpgradeRule::class);

        $progress->method('getUpgradeRule')->willReturn($rule);
        $progress->setUpgradeRule($rule);

        $this->assertSame($rule, $progress->getUpgradeRule());
    }

    public function testSetValueWithIntegerStoresValue(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $value = 5000;

        $progress->method('getValue')->willReturn($value);
        $progress->setValue($value);

        $this->assertSame($value, $progress->getValue());
    }

    public function testSetValueWithNullStoresNull(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $progress->method('getValue')->willReturn(null);
        $progress->setValue(null);

        $this->assertNull($progress->getValue());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $datetime = new \DateTimeImmutable();

        $progress->method('getCreateTime')->willReturn($datetime);
        $progress->setCreateTime($datetime);

        $this->assertSame($datetime, $progress->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $progress = $this->createMock(UpgradeProgress::class);
        $datetime = new \DateTimeImmutable();

        $progress->method('getUpdateTime')->willReturn($datetime);
        $progress->setUpdateTime($datetime);

        $this->assertSame($datetime, $progress->getUpdateTime());
    }
}
