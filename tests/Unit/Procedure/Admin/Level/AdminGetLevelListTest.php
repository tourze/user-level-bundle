<?php

namespace UserLevelBundle\Tests\Unit\Procedure\Admin\Level;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\Admin\Level\AdminGetLevelList;
use UserLevelBundle\Repository\LevelRepository;

class AdminGetLevelListTest extends TestCase
{
    private AdminGetLevelList $procedure;
    private LevelRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LevelRepository::class);
        $this->procedure = new AdminGetLevelList($this->repository);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(AdminGetLevelList::class, $this->procedure);
        $this->assertEquals('', $this->procedure->title);
    }

    public function testTitlePropertyCanBeSet(): void
    {
        $this->procedure->title = 'test title';
        $this->assertEquals('test title', $this->procedure->title);
    }

    public function testFormatItem(): void
    {
        $level = $this->createMock(Level::class);
        $expectedArray = ['id' => '1', 'title' => 'Test Level'];
        
        $level->expects($this->once())
            ->method('retrieveAdminArray')
            ->willReturn($expectedArray);
        
        $reflection = new \ReflectionClass($this->procedure);
        $method = $reflection->getMethod('formatItem');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->procedure, $level);
        
        $this->assertEquals($expectedArray, $result);
    }
}