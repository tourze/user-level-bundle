<?php

namespace UserLevelBundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Entity\AssignLog;
use UserLevelBundle\Repository\AssignLogRepository;

class AssignLogRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new AssignLogRepository($registry);
        
        $this->assertInstanceOf(AssignLogRepository::class, $repository);
    }
    
    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new AssignLogRepository($registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }
}