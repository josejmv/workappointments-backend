<?php

namespace App\Repository;

use App\Entity\Operator;
use App\Entity\Service;
use App\Entity\ServiceResume;
use App\Entity\User;
use App\Entity\UserInfo;
use App\Entity\UserInterests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Operator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operator[]    findAll()
 * @method Operator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperatorRepository extends ServiceEntityRepository
{

    private $REJECT = 0;
    private $SUCCESS = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operator::class);
    }

    public function getData($id){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    operator.id,
                    operator.active,
                    IDENTITY(operator.operatorData) AS userId,
                    operator.serviceStartHour,
                    operator.serviceEndHour
                 FROM App\Entity\Operator operator
                 WHERE operator.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }

    public function getInfo($id){
        try {
            $code = $this->SUCCESS;
            $message = 'Success';
            $error = null;
            $query = $this->getEntityManager()->createQuery(
                "SELECT info
                 FROM App\Entity\Manager manager,
                 App\Entity\User user,
                 App\Entity\UserInfo info
                 WHERE IDENTITY(manager.managerData) = user.id AND
                 IDENTITY(user.userInfo) = info.id AND
                 manager.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $code = $this->REJECT;
            $message = 'datos no obtenidos';
            $error = $th->getMessage();
            $query = 'Ha ocurrido un problema al obtener la informacion';
        }
        return $query;
    }

    public function getInterests($id){
        try {
            $code = $this->SUCCESS;
            $message = 'Success';
            $error = null;
            $query = $this->getEntityManager()->createQuery(
                "SELECT interests
                 FROM App\Entity\Manager manager,
                 App\Entity\User user,
                 App\Entity\UserInterests interests
                 WHERE IDENTITY(manager.managerData) = user.id AND
                 IDENTITY(user.userInterests) = interests.id AND
                 manager.id = ".$id
            )->getSingleResult();
        } catch (\Throwable $th) {
            $code = $this->REJECT;
            $message = 'datos no obtenidos';
            $error = $th->getMessage();
            $query = 'Ha ocurrido un problema al obtener la informacion';
        }
        return $query;
    }
    
    public function getAllByAppointmentId($appointmentId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    operator.id,
                    data.email,
                    info.id AS operatorData
                 FROM App\Entity\Operator operator
                 JOIN operator.operatorData data
                 JOIN data.userInfo info
                 WHERE IDENTITY(operator.appointment) = ".$appointmentId
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getAllByServiceId($serviceId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    resume.id AS resumeId,
                    operator.id,
                    data.id AS userId,
                    data.email,
                    info.id AS operatorData
                 FROM App\Entity\ServiceResume resume
                 JOIN resume.operator operator
                 JOIN resume.service service
                 JOIN operator.operatorData data
                 JOIN data.userInfo info
                 WHERE resume.accepted = true AND service.id = ".$serviceId
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getAllServiceResumes($serviceId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    resume.id AS resumeId,
                    operator.id AS operatorId,
                    service.id AS serviceId,
                    operator.serviceStartHour,
                    operator.serviceEndHour,
                    operator.active,
                    data.id AS userId,
                    data.email,
                    info.id AS operatorData
                 FROM App\Entity\ServiceResume resume
                 JOIN resume.operator operator
                 JOIN resume.service service
                 JOIN operator.operatorData data
                 JOIN data.userInfo info
                 WHERE service.id = ".$serviceId
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getAllAppointmentResumes($appointmentId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    resume.id AS resumeId,
                    operator.id AS operatorId,
                    appointment.id AS appointmentId,
                    operator.active,
                    data.id AS userId,
                    data.email,
                    info.id AS operatorData
                 FROM App\Entity\AppointmentResume resume
                 JOIN resume.operator operator
                 JOIN resume.appointment appointment
                 JOIN operator.operatorData data
                 JOIN data.userInfo info
                 WHERE appointment.id = ".$appointmentId
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getAllOperatorsService($serviceId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    operator.id
                 FROM App\Entity\ServiceResume resume
                 JOIN resume.operator operator
                 JOIN resume.service service
                 WHERE resume.accepted = true AND service.id =:serviceId"
            )
            ->setParameter('serviceId',$serviceId)
            ->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
}
