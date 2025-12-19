<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserLevelBundle\Controller\UpgradeProgressCrudController;
use UserLevelBundle\Entity\UpgradeProgress;

/**
 * @internal
 */
#[CoversClass(UpgradeProgressCrudController::class)]
#[RunTestsInSeparateProcesses]
class UpgradeProgressCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): \EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController
    {
        return new UpgradeProgressCrudController();
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '用户' => ['用户'];
        yield '升级规则' => ['升级规则'];
        yield '当前进度' => ['当前进度'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'user' => ['user'];
        yield 'upgradeRule' => ['upgradeRule'];
        yield 'value' => ['value'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'value' => ['value'];
    }
}
