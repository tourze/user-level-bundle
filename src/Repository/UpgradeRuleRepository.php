<?php

namespace UserLevelBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserLevelBundle\Entity\UpgradeRule;

/**
 * @method UpgradeRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeRule[]    findAll()
 * @method UpgradeRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeRuleRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpgradeRule::class);
    }
}
