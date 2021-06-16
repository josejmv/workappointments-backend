<?php

namespace App\Repository;

use App\Entity\UserInterests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserInterests|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterests|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterests[]    findAll()
 * @method UserInterests[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInterestsRepository extends ServiceEntityRepository
{

    private $REJECT_JM = 0;
    private $SUCCESS_JM = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInterests::class);
    }

    public function getInterests($id){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    interests.id,
                    interests.position,
                    interests.driveCarnet,
                    interests.hasCar,
                    interests.enterpriceCar,
                    interests.useMotocycle,
                    interests.schedule,
                    interests.workTipe,
                    interests.workIn,
                    interests.serviceWorker,
                    interests.disability,
                    interests.disabilityLevel,
                    interests.incorporate
                FROM App\Entity\UserInterests interests
                WHERE interests.id=".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $query = 'null';
        }
        return $query;
    }

    public function getByFilter($key, $attribute){
        if(is_array($attribute)){
            $query = [];
            foreach ($attribute as $value) {
                try {
                    $query[] = $this->getEntityManager()->createQuery(
                        "SELECT interests
                        FROM App\Entity\UserInterests interests
                        WHERE interests.$key LIKE :attribute"
                    )
                    ->setParameter('attribute', '%'.$value.'%')
                    ->getResult();
                } catch (\Throwable $th) {
                    $query = 'null';
                }
            }
            $formatedQuery = [];
            foreach ($query as $value) {
                foreach ($value as $object) {
                    if(!in_array($object, $formatedQuery))
                        $formatedQuery[] = $object;
                }
            }
        } else{
            try {
                $formatedQuery = $this->getEntityManager()->createQuery(
                    "SELECT interests
                    FROM App\Entity\UserInterests interests
                    WHERE interests.$key LIKE :attribute"
                )
                ->setParameter('attribute', '%'.$attribute.'%')
                ->getResult();
            } catch (\Throwable $th) {
                $formatedQuery = 'null';
            }
        }
        return $formatedQuery;
    }

    public function getByPosition($attribute){
        $query = [];
        foreach ($attribute as $value) {
            try {
                $query[] = $this->getEntityManager()->createQuery(
                    "SELECT interests
                    FROM App\Entity\UserInterests interests
                    WHERE interests.positions LIKE :attribute"
                )
                ->setParameter('attribute', '%'.$value.'%')
                ->getResult();
            } catch (\Throwable $th) {
                $query = 'null';
            }
        }
        $formatedQuery = [];
        foreach ($query as $value) {
            foreach ($value as $object) {
                if(!in_array($object, $formatedQuery))
                    $formatedQuery[] = $object;
            }
        }
        return $formatedQuery;
    }
}
