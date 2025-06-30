<?php

namespace UserLevelBundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Repository\UserLevelRelationRepository;

class UserLevelRelationRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UserLevelRelationRepository($registry);
        
        $this->assertInstanceOf(UserLevelRelationRepository::class, $repository);
    }
    
    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UserLevelRelationRepository($registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }
}