<?php

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserInterests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Admin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Admin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Admin[]    findAll()
 * @method Admin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminRepository extends ServiceEntityRepository
{
    private $REJECT = 0;
    private $SUCCESS = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Admin::class);
    }

    public function getData($id){
        try {
            $code = $this->SUCCESS;
            $message = 'Success';
            $error = null;
            $query = $this->getEntityManager()->createQuery(
                "SELECT info, interests
                 FROM App\Entity\Admin admin,
                 App\Entity\User user,
                 App\Entity\UserInfo info,
                 App\Entity\UserInterests interests
                 WHERE IDENTITY(admin.adminData) = user.id AND
                 IDENTITY(user.userInfo) = info.id AND
                 IDENTITY(user.userInterests) = interests.id AND
                 admin.id = ".$id
            )->getResult();
        } catch (\Throwable $th) {
            $code = $this->REJECT;
            $message = 'datos no obtenidos';
            $error = $th->getMessage();
            $query = 'Ha ocurrido un problema al obtener la informacion';
        }
        return array($code,$message,$error,$query);
    }

    public function getInfo($id){
        try {
            $code = $this->SUCCESS;
            $message = 'Success';
            $error = null;
            $query = $this->getEntityManager()->createQuery(
                "SELECT info
                 FROM App\Entity\Admin admin,
                 App\Entity\User user,
                 App\Entity\UserInfo info
                 WHERE IDENTITY(admin.adminData) = user.id AND
                 IDENTITY(user.userInfo) = info.id AND
                 admin.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $code = $this->REJECT;
            $message = 'datos no obtenidos';
            $error = $th->getMessage();
            $query = 'Ha ocurrido un problema al obtener la informacion';
        }
        return array($code,$message,$error,$query);
    }

    public function getInterests($id){
        try {
            $code = $this->SUCCESS;
            $message = 'Success';
            $error = null;
            $query = $this->getEntityManager()->createQuery(
                "SELECT interests
                 FROM App\Entity\Admin admin,
                 App\Entity\User user,
                 App\Entity\UserInterests interests
                 WHERE IDENTITY(admin.adminData) = user.id AND
                 IDENTITY(user.userInterests) = interests.id AND
                 admin.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $code = $this->REJECT;
            $message = 'datos no obtenidos';
            $error = $th->getMessage();
            $query = 'Ha ocurrido un problema al obtener la informacion';
        }
        return array($code,$message,$error,$query);
    }
}
