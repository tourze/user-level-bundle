<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use UserLevelBundle\Repository\UserLevelRelationRepository;

#[ORM\Entity(repositoryClass: UserLevelRelationRepository::class)]
#[ORM\Table(name: 'biz_user_level_relation', options: ['comment' => '用户拥有等级'])]
#[ORM\UniqueConstraint(name: 'unique_user_level', columns: ['user_id'])]
class UserLevelRelation implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[TrackColumn]
    #[Assert\NotNull(message: 'valid cannot be null')]
    #[Assert\Type(type: 'bool', message: 'valid must be a boolean')]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: Level::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Level $level;

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): void
    {
        $this->level = $level;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
