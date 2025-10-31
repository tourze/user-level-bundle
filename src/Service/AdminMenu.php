<?php

declare(strict_types=1);

namespace UserLevelBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use UserLevelBundle\Controller\AssignLogCrudController;
use UserLevelBundle\Controller\LevelCrudController;
use UserLevelBundle\Controller\UpgradeProgressCrudController;
use UserLevelBundle\Controller\UpgradeRuleCrudController;
use UserLevelBundle\Controller\UserLevelRelationCrudController;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        $userLevelSection = $item->addChild('用户等级管理')
            ->setAttribute('icon', 'fa fa-star-half-o')
        ;

        $userLevelSection->addChild('等级管理')
            ->setUri($this->linkGenerator->getCurdListPage(LevelCrudController::class))
            ->setAttribute('icon', 'fa fa-trophy')
        ;

        $userLevelSection->addChild('升级规则')
            ->setUri($this->linkGenerator->getCurdListPage(UpgradeRuleCrudController::class))
            ->setAttribute('icon', 'fa fa-tasks')
        ;

        $userLevelSection->addChild('用户等级关系')
            ->setUri($this->linkGenerator->getCurdListPage(UserLevelRelationCrudController::class))
            ->setAttribute('icon', 'fa fa-users')
        ;

        $userLevelSection->addChild('升级进度')
            ->setUri($this->linkGenerator->getCurdListPage(UpgradeProgressCrudController::class))
            ->setAttribute('icon', 'fa fa-bar-chart')
        ;

        $userLevelSection->addChild('等级变更日志')
            ->setUri($this->linkGenerator->getCurdListPage(AssignLogCrudController::class))
            ->setAttribute('icon', 'fa fa-history')
        ;
    }
}
