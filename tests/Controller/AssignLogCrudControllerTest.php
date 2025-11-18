<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserLevelBundle\Controller\AssignLogCrudController;
use UserLevelBundle\Entity\AssignLog;

/**
 * @internal
 */
#[CoversClass(AssignLogCrudController::class)]
#[RunTestsInSeparateProcesses]
class AssignLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): AssignLogCrudController
    {
        return new AssignLogCrudController();
    }

    public function testConfigureFields(): void
    {
        $controller = new AssignLogCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new AssignLogCrudController();
        $this->assertInstanceOf(AssignLogCrudController::class, $controller);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '用户' => ['用户'];
        yield '原等级' => ['原等级'];
        yield '新等级' => ['新等级'];
        yield '类型' => ['类型'];
        yield '分配时间' => ['分配时间'];
        yield '备注' => ['备注'];
        yield '创建者' => ['创建者'];
        yield '更新者' => ['更新者'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'user' => ['user'];
        yield 'oldLevel' => ['oldLevel'];
        yield 'newLevel' => ['newLevel'];
        yield 'type' => ['type'];
        yield 'assignTime' => ['assignTime'];
        yield 'remark' => ['remark'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'remark' => ['remark'];
    }
}
