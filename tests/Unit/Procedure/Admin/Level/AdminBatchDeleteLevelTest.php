<?php

namespace UserLevelBundle\Tests\Unit\Procedure\Admin\Level;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\Admin\Level\AdminBatchDeleteLevel;
use UserLevelBundle\Repository\LevelRepository;

class AdminBatchDeleteLevelTest extends TestCase
{
    private AdminBatchDeleteLevel $procedure;
    private LevelRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LevelRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->procedure = new AdminBatchDeleteLevel($this->repository, $this->entityManager);
    }

    public function testExecuteWithValidIds(): void
    {
        $level = $this->createMock(Level::class);
        $this->procedure->ids = ['1', '2'];
        
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['id' => ['1', '2']])
            ->willReturn([$level]);
        
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($level);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->procedure->execute();
        
        $this->assertEquals(['__message' => '删除成功'], $result);
    }

    public function testExecuteWithNoRecordsFound(): void
    {
        $this->procedure->ids = ['999'];
        
        $this->repository->expects($this->once())
            ->method('findBy')
            ->with(['id' => ['999']])
            ->willReturn([]);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('记录不存在');
        
        $this->procedure->execute();
    }
    
    public function testExecuteWithDatabaseError(): void
    {
        $level = $this->createMock(Level::class);
        $this->procedure->ids = ['1'];
        
        $this->repository->expects($this->once())
            ->method('findBy')
            ->willReturn([$level]);
        
        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($level);
        
        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Database error'));
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('批量删除失败~');
        
        $this->procedure->execute();
    }
}