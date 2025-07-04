<?php

namespace UserLevelBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserLevelBundle\Entity\AssignLog;

/**
 * @method AssignLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method AssignLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method AssignLog[]    findAll()
 * @method AssignLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssignLogRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssignLog::class);
    }
}
