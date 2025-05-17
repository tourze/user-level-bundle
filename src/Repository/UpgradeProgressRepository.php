<?php

namespace UserLevelBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserLevelBundle\Entity\UpgradeProgress;

/**
 * @method UpgradeProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UpgradeProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UpgradeProgress[]    findAll()
 * @method UpgradeProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UpgradeProgressRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UpgradeProgress::class);
    }
}
