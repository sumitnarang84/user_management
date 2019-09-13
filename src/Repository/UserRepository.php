<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);

    }

    /**
     * To find all users with name and email only
     * @return Array User[]
     */
    public function findAllNameAndEmail()
    {
        $query = $this->createQueryBuilder('u')
            ->select('u.id, u.name, u.email');
        ;

        return $query->getQuery()->getResult();
    }

}
