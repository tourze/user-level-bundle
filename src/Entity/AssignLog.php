<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use UserLevelBundle\Repository\AssignLogRepository;

#[ORM\Entity(repositoryClass: AssignLogRepository::class)]
#[ORM\Table(name: 'user_level_assign_log', options: ['comment' => '用户等级升降级记录'])]
class AssignLog implements AdminArrayInterface
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $newLevel = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Level $oldLevel = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserInterface $user = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['comment' => '类型0降级，1升级'])]
    private int $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => ''])]
    private ?\DateTimeInterface $assignTime = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '备注', 'default' => ''])]
    private string $remark;

    #[CreatedByColumn]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    private ?string $updatedBy = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getAssignTime(): ?\DateTimeInterface
    {
        return $this->assignTime;
    }

    public function setAssignTime(?\DateTimeInterface $assignTime): void
    {
        $this->assignTime = $assignTime;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }

    public function retrieveAdminArray(): array
    {
        return [
            'newLevelInfo' => $this->getNewLevel()->retrieveAdminArray(),
            'oldLevelInfo' => $this->getOldLevel()->retrieveAdminArray(),
            'userInfo' => [
                'id' => $this->getUser()?->getId(),
                'nickName' => $this->getUser()?->getNickName(),
                'username' => $this->getUser()?->getUsername(),
            ],
            'assignTime' => $this->getAssignTime()?->format('Y-m-d H:i:s'),
            'createTime' => $this->getCreateTime()->format('Y-m-d H:i:s'),
        ];
    }

    public function getNewLevel(): ?Level
    {
        return $this->newLevel;
    }

    public function setNewLevel(?Level $newLevel): void
    {
        $this->newLevel = $newLevel;
    }

    public function getOldLevel(): ?Level
    {
        return $this->oldLevel;
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
    }

    public function setOldLevel(?Level $oldLevel): void
    {
        $this->oldLevel = $oldLevel;
    }}
