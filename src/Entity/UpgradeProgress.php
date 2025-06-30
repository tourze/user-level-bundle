<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use UserLevelBundle\Repository\UpgradeProgressRepository;

#[ORM\Entity(repositoryClass: UpgradeProgressRepository::class)]
#[ORM\Table(name: 'biz_user_level_upgrade_progress', options: ['comment' => '用户等级升级进度'])]
class UpgradeProgress implements Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'upgradeRules')]
    #[ORM\JoinColumn(nullable: false)]
    private UpgradeRule $upgradeRule;

    #[ORM\Column(options: ['comment' => '当前进度'])]
    private ?int $value = null;


    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getUpgradeRule(): UpgradeRule
    {
        return $this->upgradeRule;
    }

    public function setUpgradeRule(UpgradeRule $upgradeRule): void
    {
        $this->upgradeRule = $upgradeRule;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }
    public function __toString(): string
    {
        return (string) $this->id;
    }
}
