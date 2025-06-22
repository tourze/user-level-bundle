<?php

namespace UserLevelBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserLevelBundle\Repository\LevelRepository;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ORM\Table(name: 'biz_user_level', options: ['comment' => '用户等级'])]
class Level implements AdminArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '等级名称'])]
    private string $title;

    #[ORM\Column(type: Types::SMALLINT, unique: true, options: ['comment' => '等级值'])]
    private int $level;

    #[ORM\OneToMany(targetEntity: UpgradeRule::class, mappedBy: 'userLevel', cascade: ['persist'], orphanRemoval: true)]
    private Collection $upgradeRules;

    #[TrackColumn]
    private ?bool $valid = false;


    public function __construct()
    {
        $this->upgradeRules = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

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
