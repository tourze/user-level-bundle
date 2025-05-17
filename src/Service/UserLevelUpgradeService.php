<?php

namespace UserLevelBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Repository\LevelRepository;
use UserLevelBundle\Repository\UpgradeProgressRepository;
use UserLevelBundle\Repository\UserLevelRelationRepository;

/**
 * 用户等级升级服务
 */
class UserLevelUpgradeService
{
    public function __construct(
        private readonly UserLevelRelationRepository $userLevelRelationRepository,
        private readonly LevelRepository $levelRepository,
        private readonly UpgradeProgressRepository $levelUpgradeProgressRepository,
    ) {
    }

    public function upgrade(UserInterface $user): void
    {
        // 检查是否能升级
        // 查找用户当前等级
        $currentLevel = $this->userLevelRelationRepository->findOneBy(['user' => $user]);
        // 获取用户下一等级
        if (!$currentLevel) {// 没等级找一个最低级的
            $level = $this->levelRepository->findOneBy(['valid' => true], ['level' => 'ASC']);
        } else {
            /** @var Level $level */
            $level = $this->levelRepository->createQueryBuilder('a')
                ->where('a.valid = :valid AND a.level > :level')
                ->setParameter('valid', 'true')
                ->setParameter('level', $currentLevel->getLevel()->getLevel())
                ->addOrderBy('a.level', 'ASC')
                ->getQuery()
                ->getResult();
        }
        if (!$level) {
            return;
        }
        // 获取该等级的升级条件
        $upgradeRules = $level->getUpgradeRules();

        // 获取用户当前升级进度
        $this->levelUpgradeProgressRepository->findBy([
            'user' => $user,
        ]);

        // 进度对比
        // TODO

        // 升级
    }

    /**
     * 降级
     *
     * @return void
     */
    public function degrade(UserInterface $user)
    {
    }
}
