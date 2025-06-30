<?php

namespace UserLevelBundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Repository\UpgradeProgressRepository;

class UpgradeProgressRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UpgradeProgressRepository($registry);
        
        $this->assertInstanceOf(UpgradeProgressRepository::class, $repository);
    }
    
    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UpgradeProgressRepository($registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }
}