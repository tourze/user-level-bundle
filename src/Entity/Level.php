<?php

namespace UserLevelBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserLevelBundle\Repository\LevelRepository;

/**
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ORM\Table(name: 'biz_user_level', options: ['comment' => '用户等级'])]
class Level implements AdminArrayInterface, \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '等级名称'])]
    #[Assert\NotBlank(message: 'title cannot be blank')]
    #[Assert\Length(max: 100, maxMessage: 'title cannot exceed 100 characters')]
    private string $title;

    #[ORM\Column(type: Types::SMALLINT, unique: true, options: ['comment' => '等级值'])]
    #[Assert\NotNull(message: 'level cannot be null')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'level must be greater than or equal to 0')]
    private int $level;

    /**
     * @var Collection<int, UpgradeRule>
     */
    #[ORM\OneToMany(targetEntity: UpgradeRule::class, mappedBy: 'userLevel', cascade: ['persist'], orphanRemoval: true)]
    private Collection $upgradeRules;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否有效'])]
    #[TrackColumn]
    #[Assert\NotNull(message: 'valid cannot be null')]
    #[Assert\Type(type: 'bool', message: 'valid must be a boolean')]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->upgradeRules = new ArrayCollection();
    }

    /**
     * @return Collection<int, UpgradeRule>
     */
    public function getUpgradeRules(): Collection
    {
        return $this->upgradeRules;
    }

    public function addUpgradeRule(UpgradeRule $upgradeRule): static
    {
        if (!$this->upgradeRules->contains($upgradeRule)) {
            $this->upgradeRules->add($upgradeRule);
            $upgradeRule->setLevel($this);
        }

        return $this;
    }

    public function removeUpgradeRule(UpgradeRule $upgradeRule): static
    {
        if ($this->upgradeRules->removeElement($upgradeRule)) {
            $upgradeRule->setLevel(null);
        }

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        return [
            'level' => $this->getLevel(),
            'title' => $this->getTitle(),
            'id' => $this->getId(),
        ];
    }

    public function __toString(): string
    {
        return $this->title ?? 'Level#' . ($this->id ?? 'new');
    }
}
