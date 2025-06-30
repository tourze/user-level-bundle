<?php

namespace UserLevelBundle\Tests\Unit\Procedure\Admin\Level;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UserLevelBundle\Entity\AssignLog; 
use UserLevelBundle\Procedure\Admin\Level\GetLevelLogsByBizUserId;
use UserLevelBundle\Repository\AssignLogRepository;

class GetLevelLogsByBizUserIdTest extends TestCase
{
    private GetLevelLogsByBizUserId $procedure;
    private AssignLogRepository $repository;
    private UserLoaderInterface $userLoader;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AssignLogRepository::class);
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->procedure = new GetLevelLogsByBizUserId($this->repository, $this->userLoader);
    }

    public function testProcedureInstantiation(): void
    {
        $this->assertInstanceOf(GetLevelLogsByBizUserId::class, $this->procedure);
    }

    public function testUserIdPropertyCanBeSet(): void
    {
        $this->procedure->userId = 'user123';
        $this->assertEquals('user123', $this->procedure->userId);
    }

    public function testFormatItem(): void
    {
        $assignLog = $this->createMock(AssignLog::class);
        $expectedArray = ['id' => '1', 'user' => 'test'];
        
        $assignLog->expects($this->once())
            ->method('retrieveAdminArray')
            ->willReturn($expectedArray);
        
        $reflection = new \ReflectionClass($this->procedure);
        $method = $reflection->getMethod('formatItem');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->procedure, $assignLog);
        
        $this->assertEquals($expectedArray, $result);
    }
}