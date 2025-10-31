<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserLevelBundle\Repository\UpgradeRuleRepository;

#[ORM\Entity(repositoryClass: UpgradeRuleRepository::class)]
#[ORM\Table(name: 'biz_user_level_upgrade_rule', options: ['comment' => '用户等级升级规则'])]
class UpgradeRule implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '规则名称'])]
    #[Assert\NotBlank(message: 'title cannot be blank')]
    #[Assert\Length(max: 100, maxMessage: 'title cannot exceed 100 characters')]
    private string $title;

    #[ORM\Column(options: ['comment' => '到达数值'])]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'value must be greater than or equal to 0')]
    #[Assert\Type(type: 'int', message: 'value must be an integer')]
    private int $value = 0;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'upgradeRules', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_level_id', nullable: true, onDelete: 'CASCADE')]
    private ?Level $userLevel;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[TrackColumn]
    #[Assert\NotNull(message: 'valid cannot be null')]
    #[Assert\Type(type: 'bool', message: 'valid must be a boolean')]
    private bool $valid = false;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getUserLevel(): ?Level
    {
        return $this->userLevel;
    }

    public function setUserLevel(?Level $userLevel): void
    {
        $this->userLevel = $userLevel;
    }

    public function getLevel(): ?Level
    {
        return $this->userLevel;
    }

    public function setLevel(?Level $level): void
    {
        $this->userLevel = $level;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function __toString(): string
    {
        return $this->title ?? 'UpgradeRule#' . ($this->id ?? 'new');
    }
}
