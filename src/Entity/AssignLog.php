<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use UserLevelBundle\Repository\AssignLogRepository;

/**
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: AssignLogRepository::class)]
#[ORM\Table(name: 'user_level_assign_log', options: ['comment' => '用户等级升降级记录'])]
class AssignLog implements AdminArrayInterface, \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

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
    #[Assert\Choice(choices: [0, 1], message: 'type must be 0 (downgrade) or 1 (upgrade)')]
    private int $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '分配时间'])]
    #[Assert\Type(type: '\DateTimeInterface', message: 'assignTime must be a DateTimeInterface')]
    private ?\DateTimeInterface $assignTime = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '备注', 'default' => ''])]
    #[Assert\Length(max: 100, maxMessage: 'remark cannot exceed 100 characters')]
    #[Assert\NotBlank(message: 'remark cannot be blank')]
    private string $remark;

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

    /**
     * @return array<string, mixed>
     */
    public function retrieveAdminArray(): array
    {
        $user = $this->getUser();
        $userInfo = [];
        if (null !== $user) {
            // 检查是否是 BizUser 实例
            if (method_exists($user, 'getId')) {
                $userInfo['id'] = $user->getId();
            }
            if (method_exists($user, 'getNickName')) {
                $userInfo['nickName'] = $user->getNickName();
            }
            // UserInterface 保证有 getUserIdentifier 方法
            $userInfo['username'] = $user->getUserIdentifier();
        }

        return [
            'newLevelInfo' => $this->getNewLevel()?->retrieveAdminArray() ?? [],
            'oldLevelInfo' => $this->getOldLevel()?->retrieveAdminArray() ?? [],
            'userInfo' => $userInfo,
            'assignTime' => $this->getAssignTime()?->format('Y-m-d H:i:s') ?? null,
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s') ?? null,
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

    public function setOldLevel(?Level $oldLevel): void
    {
        $this->oldLevel = $oldLevel;
    }

    public function __toString(): string
    {
        return sprintf('AssignLog#%s', $this->getId() ?? 'new');
    }
}
