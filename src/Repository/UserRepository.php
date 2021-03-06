<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function search($filter){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT user
                 FROM App\Entity\User user
                 WHERE user.email LIKE :email"
            )
            ->setParameter("email","%$filter%")
            ->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
}
