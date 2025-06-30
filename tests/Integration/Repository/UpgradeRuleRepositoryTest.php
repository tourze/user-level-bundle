<?php

namespace UserLevelBundle\Tests\Integration\Repository;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use UserLevelBundle\Repository\UpgradeRuleRepository;

class UpgradeRuleRepositoryTest extends TestCase
{
    public function testRepositoryInstantiation(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UpgradeRuleRepository($registry);
        
        $this->assertInstanceOf(UpgradeRuleRepository::class, $repository);
    }
    
    public function testRepositoryImplementsServiceEntityRepository(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $repository = new UpgradeRuleRepository($registry);
        
        $this->assertInstanceOf(\Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository::class, $repository);
    }
}