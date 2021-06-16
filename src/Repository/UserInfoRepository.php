<?php

namespace App\Repository;

use App\Entity\UserInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInfo[]    findAll()
 * @method UserInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserInfoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserInfo::class);
    }

    public function searchByFirstName($filter){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT userInfo
                 FROM App\Entity\UserInfo userInfo
                 WHERE userInfo.firstName LIKE :firstName"
            )
            ->setParameter("firstName",$filter."%")
            ->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function searchByCelphone($filter){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT userInfo
                 FROM App\Entity\UserInfo userInfo
                 WHERE userInfo.celphone LIKE :celphone"
            )
            ->setParameter("celphone",$filter."%")
            ->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getData($id){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    userInfo.firstName,
                    userInfo.lastName,
                    userInfo.birthday,
                    userInfo.celphone,
                    userInfo.phone,
                    userInfo.postalCode,
                    userInfo.country,
                    userInfo.secundaryEmail,
                    userInfo.residence,
                    userInfo.province,
                    userInfo.city
                 FROM App\Entity\UserInfo userInfo
                 WHERE userInfo.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
}
