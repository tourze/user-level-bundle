<?php

namespace UserLevelBundle\Tests\Procedure\Admin\Level;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use UserLevelBundle\Entity\Level;
use UserLevelBundle\Procedure\Admin\Level\AdminCreateLevel;
use UserLevelBundle\Repository\LevelRepository;

class AdminCreateLevelTest extends TestCase
{
    private AdminCreateLevel $procedure;
    private \PHPUnit\Framework\MockObject\MockObject|LevelRepository $levelRepository;
    private \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->levelRepository = $this->createMock(LevelRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->procedure = new AdminCreateLevel(
            $this->levelRepository,
            $this->entityManager
        );
    }

    public function testExecute_withValidData_createsLevel(): void
    {
        // 设置输入数据
        $this->procedure->title = 'VIP会员';
        $this->procedure->level = 3;
        $this->procedure->valid = true;

        // 验证实体管理器操作
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Level $level) {
                return $level->getTitle() === 'VIP会员'
                    && $level->getLevel() === 3
                    && $level->isValid() === true;
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行过程
        $result = $this->procedure->execute();

        // 验证返回值
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('创建成功', $result['__message']);
    }

    public function testExecute_withDuplicateData_throwsApiException(): void
    {
        // 设置输入数据
        $this->procedure->title = 'VIP会员';
        $this->procedure->level = 3;
        $this->procedure->valid = true;

        // 模拟唯一约束违反异常
        $uniqueConstraintException = $this->createMock(UniqueConstraintViolationException::class);

        // 设置实体管理器抛出异常
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Level::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($uniqueConstraintException);

        // 预期抛出API异常
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('创建时发现重复数据');

        // 执行过程
        $this->procedure->execute();
    }
    
    public function testExecute_withNullValid_usesDefaultFalse(): void
    {
        // 设置输入数据，不包括valid字段
        $this->procedure->title = 'VIP会员';
        $this->procedure->level = 3;
        // 不设置valid，使用默认值

        // 验证实体管理器操作
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (Level $level) {
                return $level->getTitle() === 'VIP会员'
                    && $level->getLevel() === 3
                    && $level->isValid() === false; // 默认值为false
            }));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行过程
        $result = $this->procedure->execute();

        // 验证返回值
        $this->assertIsArray($result);
        $this->assertArrayHasKey('__message', $result);
        $this->assertEquals('创建成功', $result['__message']);
    }
} 