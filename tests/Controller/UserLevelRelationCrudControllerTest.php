<?php

declare(strict_types=1);

namespace UserLevelBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use UserLevelBundle\Controller\UserLevelRelationCrudController;
use UserLevelBundle\Entity\UserLevelRelation;

/**
 * @internal
 */
#[CoversClass(UserLevelRelationCrudController::class)]
#[RunTestsInSeparateProcesses]
class UserLevelRelationCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getControllerService(): UserLevelRelationCrudController
    {
        return new UserLevelRelationCrudController();
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): \Generator
    {
        yield 'ID' => ['ID'];
        yield '用户' => ['用户'];
        yield '用户等级' => ['用户等级'];
        yield '是否有效' => ['是否有效'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): \Generator
    {
        yield 'user' => ['user'];
        yield 'level' => ['level'];
        yield 'valid' => ['valid'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): \Generator
    {
        yield 'valid' => ['valid'];
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(UserLevelRelation::class, UserLevelRelationCrudController::getEntityFqcn());
    }

    public function testConfigureFields(): void
    {
        $controller = new UserLevelRelationCrudController();
        $fields = iterator_to_array($controller->configureFields('index'));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new UserLevelRelationCrudController();
        $this->assertInstanceOf(UserLevelRelationCrudController::class, $controller);
    }
}
