<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\Operator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Appointments|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointments|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointments[]    findAll()
 * @method Appointments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }
    
    public function getAll(){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    appointment.id,
                    IDENTITY(appointment.manager) AS managerId,
                    IDENTITY(appointment.admin) AS adminId,
                    appointment.title,
                    appointment.description,
                    appointment.direction,
                    appointment.date,
                    appointment.hour,
                    appointment.quantityEmployees,
                    appointment.tipeWork,
                    appointment.active,
                    appointment.appointmentType,
                    appointment.newCreated
                 FROM App\Entity\Appointment appointment"
            )->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function search($title){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT appointment
                 FROM App\Entity\Appointment appointment
                 WHERE appointment.title LIKE :title
                 AND appointment.active = true"
            )
            ->setParameter("title","$title%")
            ->getResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
    
    public function getById($appointmentId){
        try {
            $query = $this->getEntityManager()->createQuery(
                "SELECT
                    appointment.id,
                    IDENTITY(appointment.manager) AS managerId,
                    IDENTITY(appointment.admin) AS adminId,
                    appointment.title,
                    appointment.description,
                    appointment.direction,
                    appointment.date,
                    appointment.hour,
                    appointment.quantityEmployees,
                    appointment.tipeWork,
                    appointment.active,
                    appointment.appointmentType,
                    appointment.newCreated
                 FROM App\Entity\Appointment appointment
                 WHERE appointment.id =".$appointmentId
            )->getSingleResult();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
        }
        return $query;
    }
}
