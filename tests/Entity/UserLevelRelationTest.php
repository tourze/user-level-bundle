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
        $relation = new UserLevelRelation();
        $this->assertFalse($relation->isValid());
    }

    public function testSetValidWithTrueStoresTrue(): void
    {
        $relation = new UserLevelRelation();
        $relation->setValid(true);

        $this->assertTrue($relation->isValid());
    }

    public function testSetValidWithFalseStoresFalse(): void
    {
        $relation = new UserLevelRelation();
        $relation->setValid(false);

        $this->assertFalse($relation->isValid());
    }

    public function testSetLevelWithLevelObjectStoresLevel(): void
    {
        $relation = new UserLevelRelation();
        $level = new Level();
        $level->setTitle('Test Level');
        $level->setLevel(1);

        $relation->setLevel($level);

        $this->assertSame($level, $relation->getLevel());
    }

    public function testSetUserWithUserObjectStoresUser(): void
    {
        $relation = new UserLevelRelation();
        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }

            public function getPassword(?string $service = null): ?string
            {
                return null;
            }

            public function getSalt(): ?string
            {
                return null;
            }

            public function getUsername(): string
            {
                return 'test@example.com';
            }

            public function eraseCredentials(): void
            {
            }

            public function getUserIdentifier(): string
            {
                return 'test@example.com';
            }

            public function getPasswordSalt(): ?string
            {
                return null;
            }
        };

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
