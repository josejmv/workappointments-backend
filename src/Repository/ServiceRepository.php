<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }
    
    public function search($filter){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT service
                 FROM App\Entity\Service service
                 WHERE service.title LIKE :title
                 AND service.active = true"
            )
            ->setParameter("title","%$filter%")
            ->getResult();
        } catch (\Throwable $th) {
            $query = [];
        }
        return $query;
    }
}