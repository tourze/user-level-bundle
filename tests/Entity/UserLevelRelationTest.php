<?php

namespace UserLevelBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Entity\UserLevelRelation;

/**
 * @internal
 */
#[CoversClass(UserLevelRelation::class)]
final class UserLevelRelationTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new UserLevelRelation();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'valid' => ['valid', true],
        ];
    }

    public function testGetIdWhenNewInstanceReturnsNull(): void
    {
        $relation = new UserLevelRelation();
        $this->assertNull($relation->getId());
    }

    public function testIsValidWithDefaultValueReturnsFalse(): void
    {
        $relation = $this->createMock(UserLevelRelation::class);
        $relation->method('isValid')->willReturn(false);
        $this->assertFalse($relation->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $relation = $this->createMock(UserLevelRelation::class);
        $relation->method('isValid')->willReturn(true);
        $relation->setValid(true);

        $this->assertTrue($relation->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $relation = $this->createMock(UserLevelRelation::class);
        $relation->method('isValid')->willReturn(false);
        $relation->setValid(false);

        $this->assertFalse($relation->isValid());
    }

    public function testSetLevelWithLevelObjectStoresLevel(): void
    {
        $relation = $this->createMock(UserLevelRelation::class);
        $level = $this->createMock(Level::class);

        $relation->method('getLevel')->willReturn($level);
        $relation->setLevel($level);

        $this->assertSame($level, $relation->getLevel());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $relation = $this->createMock(UserLevelRelation::class);
        $user = $this->createMock(UserInterface::class);

        $relation->method('getUser')->willReturn($user);
        $relation->setUser($user);

        $this->assertSame($user, $relation->getUser());
    }

    public function testSetCreateTimeWithDateTimeStoresCreateTime(): void
    {
        $relation = new UserLevelRelation();
        $datetime = new \DateTimeImmutable();

        $relation->setCreateTime($datetime);

        $this->assertSame($datetime, $relation->getCreateTime());
    }

    public function testSetUpdateTimeWithDateTimeStoresUpdateTime(): void
    {
        $relation = new UserLevelRelation();
        $datetime = new \DateTimeImmutable();

        $relation->setUpdateTime($datetime);

        $this->assertSame($datetime, $relation->getUpdateTime());
    }
}
