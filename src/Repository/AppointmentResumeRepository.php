<?php

namespace App\Repository;

use App\Entity\AppointmentResume;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AppointmentResume|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppointmentResume|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppointmentResume[]    findAll()
 * @method AppointmentResume[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentResumeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppointmentResume::class);
    }
    
    public function getResume($appointmentId, $operatorId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    resume.personal
                 FROM App\Entity\AppointmentResume resume
                 WHERE IDENTITY(resume.appointment) =:appointmentId AND IDENTITY(resume.operator) =:operatorId"
            )
            ->setParameter("appointmentId",$appointmentId)
            ->setParameter("operatorId",$operatorId)
            ->getSingleResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function get($resumeId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT 
                    resume.id,
                    resume.personal,
                    resume.status,
                    resume.firstPosition,
                    resume.interviewer,
                    resume.secondPosition,
                    resume.interviewer2,
                    resume.expensive,
                    resume.wantWorkWithUs,
                    resume.firstStep,
                    resume.citedForPersonal,
                    resume.accepted,
                    resume.disabled
                 FROM App\Entity\AppointmentResume resume
                 WHERE resume.id = ".$resumeId
            )->getSingleResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getByOperatorId($operatorId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    resume.id,
                    IDENTITY(resume.operator) AS operatorId,
                    IDENTITY(resume.appointment) AS appointmentId,
                    resume.personal,
                    resume.status,
                    resume.firstPosition,
                    resume.interviewer,
                    resume.secondPosition,
                    resume.interviewer2,
                    resume.expensive,
                    resume.wantWorkWithUs,
                    resume.firstStep,
                    resume.citedForPersonal,
                    resume.accepted,
                    resume.disabled
                 FROM App\Entity\AppointmentResume resume
                 WHERE IDENTITY(resume.operator) =".$operatorId
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
}
