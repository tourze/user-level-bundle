<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserLevelBundle\Controller\LevelCrudController;
use UserLevelBundle\Entity\Level;

/**
 * @internal
 */
#[CoversClass(LevelCrudController::class)]
#[RunTestsInSeparateProcesses]
class LevelCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): LevelCrudController
    {
        return new LevelCrudController();
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '等级名称' => ['等级名称'];
        yield '等级值' => ['等级值'];
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
        yield 'level' => ['level'];
        yield 'valid' => ['valid'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'title' => ['title'];
        yield 'level' => ['level'];
    }

    public function testConfigureFields(): void
    {
        $controller = new LevelCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new LevelCrudController();
        $this->assertInstanceOf(LevelCrudController::class, $controller);
    }
}
