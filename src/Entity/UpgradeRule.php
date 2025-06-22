<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserLevelBundle\Repository\UpgradeRuleRepository;

#[ORM\Entity(repositoryClass: UpgradeRuleRepository::class)]
#[ORM\Table(name: 'biz_user_level_upgrade_rule', options: ['comment' => '用户等级升级规则'])]
class UpgradeRule implements Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '规则名称'])]
    private string $title;

    #[ORM\Column(options: ['comment' => '到达数值'])]
    private ?int $value = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'upgradeRules')]
    #[ORM\JoinColumn(nullable: false)]
    private Level $level;

    #[TrackColumn]
    private ?bool $valid = false;


    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): void
    {
        $this->value = $value;
    }

    public function getLevel(): Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): void
    {
        $this->level = $level;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'UpgradeRule#' . ($this->id ?? 'new');
    }
}
