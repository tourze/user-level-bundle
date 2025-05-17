<?php

namespace UserLevelBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use UserLevelBundle\Entity\UserLevelRelation;

/**
 * @method UserLevelRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLevelRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLevelRelation[]    findAll()
 * @method UserLevelRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLevelRelationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLevelRelation::class);
    }
}
