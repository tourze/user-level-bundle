<?php

namespace UserLevelBundle\Tests\Unit\Procedure\Admin\Level;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\Admin\Level\AdminDeleteLevel;
use UserLevelBundle\Repository\LevelRepository;

class AdminDeleteLevelTest extends TestCase
{
    private AdminDeleteLevel $procedure;
    private LevelRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LevelRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->procedure = new AdminDeleteLevel($this->repository, $this->entityManager);
    }

    public function testExecuteWithValidId(): void
    {
        $level = $this->createMock(Level::class);
        $this->procedure->id = '1';
        
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '1'])
            ->willReturn($level);
        
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($level);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->procedure->execute();
        
        $this->assertEquals(['__message' => '删除成功'], $result);
    }

    public function testExecuteWithInvalidId(): void
    {
        $this->procedure->id = '999';
        
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '999'])
            ->willReturn(null);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('记录不存在');
        
        $this->procedure->execute();
    }
}