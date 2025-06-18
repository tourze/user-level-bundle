<?php

namespace UserLevelBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use UserLevelBundle\Repository\LevelRepository;

#[AsPermission(title: '用户等级')]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ORM\Table(name: 'biz_user_level', options: ['comment' => '用户等级'])]
class Level implements AdminArrayInterface
{
    use TimestampableAware;
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, options: ['comment' => '等级名称'])]
    private string $title;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(type: Types::SMALLINT, unique: true, options: ['comment' => '等级值'])]
    private int $level;

    #[CurdAction(label: '升级规则')]
    #[ORM\OneToMany(targetEntity: UpgradeRule::class, mappedBy: 'userLevel', cascade: ['persist'], orphanRemoval: true)]
    private Collection $upgradeRules;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

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

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }public function retrieveAdminArray(): array
    {
        return [
            'level' => $this->getLevel(),
            'title' => $this->getTitle(),
            'id' => $this->getId(),
        ];
    }
}
