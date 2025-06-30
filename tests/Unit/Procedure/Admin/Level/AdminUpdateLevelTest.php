<?php

namespace UserLevelBundle\Tests\Unit\Procedure\Admin\Level;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\Admin\Level\AdminUpdateLevel;
use UserLevelBundle\Repository\LevelRepository;

class AdminUpdateLevelTest extends TestCase
{
    private AdminUpdateLevel $procedure;
    private LevelRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(LevelRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->procedure = new AdminUpdateLevel($this->repository, $this->entityManager);
    }

    public function testExecuteWithValidData(): void
    {
        $level = $this->createMock(Level::class);
        $this->procedure->id = '1';
        $this->procedure->title = 'Updated Level';
        $this->procedure->level = 5;
        $this->procedure->valid = true;
        
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => '1'])
            ->willReturn($level);
        
        $level->expects($this->once())
            ->method('setTitle')
            ->with('Updated Level');
        
        $level->expects($this->once())
            ->method('setLevel')
            ->with(5);
        
        $level->expects($this->once())
            ->method('setValid')
            ->with(true);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($level);
        
        $this->entityManager->expects($this->once())
            ->method('flush');
        
        $result = $this->procedure->execute();
        
        $this->assertEquals(['__message' => '编辑成功'], $result);
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
    
    public function testExecuteWithUniqueConstraintViolation(): void
    {
        $level = $this->createMock(Level::class);
        $this->procedure->id = '1';
        $this->procedure->title = 'Duplicate Level';
        $this->procedure->level = 5;
        $this->procedure->valid = true;
        
        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($level);
        
        $level->expects($this->once())
            ->method('setTitle')
            ->with('Duplicate Level');
        
        $level->expects($this->once())
            ->method('setLevel')
            ->with(5);
        
        $level->expects($this->once())
            ->method('setValid')
            ->with(true);
        
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($level);
        
        $uniqueException = $this->createMock(UniqueConstraintViolationException::class);
        
        $this->entityManager->expects($this->once())
            ->method('flush')
            ->willThrowException($uniqueException);
        
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('更新时发现重复数据');
        
        $this->procedure->execute();
    }
}