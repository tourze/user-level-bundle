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
        $progress = new UpgradeProgress();
        $this->assertNull($progress->getId());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $progress = new UpgradeProgress();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'testuser';
            }
        };

        $progress->setUser($user);

        $this->assertSame($user, $progress->getUser());
    }

    public function testSetUpgradeRuleWithRuleObjectStoresRule(): void
    {
        $progress = new UpgradeProgress();
        $rule = new UpgradeRule();
        $rule->setTitle('test_rule');

        $progress->setUpgradeRule($rule);

        $this->assertSame($rule, $progress->getUpgradeRule());
    }

    public function testSetValueWithIntegerStoresValue(): void
    {
        $progress = new UpgradeProgress();
        $value = 5000;

        $progress->setValue($value);

        $this->assertSame($value, $progress->getValue());
    }

    public function testSetValueWithNullStoresNull(): void
    {
        $progress = new UpgradeProgress();
        $progress->setValue(null);

        $this->assertNull($progress->getValue());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $progress = new UpgradeProgress();
        $datetime = new \DateTimeImmutable();

        $progress->setCreateTime($datetime);

        $this->assertSame($datetime, $progress->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $progress = new UpgradeProgress();
        $datetime = new \DateTimeImmutable();

        $progress->setUpdateTime($datetime);

        $this->assertSame($datetime, $progress->getUpdateTime());
    }
}
