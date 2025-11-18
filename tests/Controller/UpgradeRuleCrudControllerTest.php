<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserLevelBundle\Controller\UpgradeRuleCrudController;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * @internal
 */
#[CoversClass(UpgradeRuleCrudController::class)]
#[RunTestsInSeparateProcesses]
class UpgradeRuleCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UpgradeRuleCrudController
    {
        return new UpgradeRuleCrudController();
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '规则名称' => ['规则名称'];
        yield '到达数值' => ['到达数值'];
        yield '目标等级' => ['目标等级'];
        yield '是否有效' => ['是否有效'];
        yield '创建者' => ['创建者'];
        yield '更新者' => ['更新者'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'title' => ['title'];
        yield 'value' => ['value'];
        yield 'userLevel' => ['userLevel'];
        yield 'valid' => ['valid'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'title' => ['title'];
        yield 'value' => ['value'];
    }

    public function testConfigureFields(): void
    {
        $controller = new UpgradeRuleCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new UpgradeRuleCrudController();
        $this->assertInstanceOf(UpgradeRuleCrudController::class, $controller);
    }
}
