<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use UserLevelBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup for AdminMenu tests
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $adminMenu = static::getService(AdminMenu::class);

        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testInvokeAddsMenuItems(): void
    {
        $rootItem = $this->createMock(ItemInterface::class);
        $sectionItem = $this->createMock(ItemInterface::class);
        $menuItem = $this->createMock(ItemInterface::class);

        $rootItem->expects($this->once())
            ->method('addChild')
            ->with('用户等级管理')
            ->willReturn($sectionItem)
        ;

        $sectionItem->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-star-half-o')
            ->willReturn($sectionItem)
        ;

        $sectionItem->expects($this->exactly(5))
            ->method('addChild')
            ->willReturn($menuItem)
        ;

        $menuItem->expects($this->exactly(5))
            ->method('setUri')
            ->willReturn($menuItem)
        ;

        $menuItem->expects($this->exactly(5))
            ->method('setAttribute')
            ->willReturn($menuItem)
        ;

        $adminMenu = static::getService(AdminMenu::class);
        $adminMenu($rootItem);
    }

    public function testAdminMenuCanBeInstantiated(): void
    {
        $adminMenu = static::getService(AdminMenu::class);

        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }
}
