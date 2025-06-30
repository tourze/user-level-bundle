<?php

namespace UserLevelBundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Repository\LevelRepository;

class LevelRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new LevelRepository($registry);
        
        $this->assertInstanceOf(LevelRepository::class, $repository);
    }
    
    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new LevelRepository($registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }
}