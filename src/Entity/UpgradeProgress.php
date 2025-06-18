<?php

namespace UserLevelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use UserLevelBundle\Repository\UpgradeProgressRepository;

#[ORM\Entity(repositoryClass: UpgradeProgressRepository::class)]
#[ORM\Table(name: 'biz_user_level_upgrade_progress', options: ['comment' => '用户等级升级进度'])]
class UpgradeProgress
{
    use TimestampableAware;
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'upgradeRules')]
    #[ORM\JoinColumn(nullable: false)]
    private UpgradeRule $upgradeRule;

    #[ORM\Column(options: ['comment' => '当前进度'])]
    private ?int $value = null;

    public function getId(): ?string
    {
        return $this->id;
    }

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
    }}
