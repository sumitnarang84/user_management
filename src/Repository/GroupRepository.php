<?php

namespace App\Repository;

use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    /**
     * To find all groups with name only
     * @return Array User[]
     */
    public function findAllName()
    {
        $query = $this->createQueryBuilder('g')
            ->select('g.id, g.name');

        return $query->getQuery()->getResult();
    }

    /**
     * To find all groups with name only excluding the exisiting user groups
     * @return Array User[]
     */
    public function findAllNameExcluding($groupIds)
    {
        $qb    = $this->createQueryBuilder('g');
        $query = $qb->select('g.id, g.name');

        if (count($groupIds) > 0) {
            $qb->where($qb->expr()->notIn('g.id',  $groupIds));
        }

        return $query->getQuery()->getResult();
    }

    
    

    
}
