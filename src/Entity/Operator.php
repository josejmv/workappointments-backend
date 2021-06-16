<?php

namespace App\Entity;

use App\Repository\OperatorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OperatorRepository::class)
 */
class Operator
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $operatorData;

    /**
     * @ORM\ManyToMany(targetEntity=Appointment::class, inversedBy="operators", cascade={"persist", "remove"})
     */
    private $appointments;

    /**
     * @ORM\OneToMany(targetEntity=AppointmentResume::class, mappedBy="operator", cascade={"persist", "remove"})
     */
    private $appointmentResume;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="operators", cascade={"persist", "remove"})
     */
    private $services;

    /**
     * @ORM\OneToMany(targetEntity=ServiceResume::class, mappedBy="operator", cascade={"persist", "remove"})
     */
    private $serviceResume;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->appointments = new ArrayCollection();
        $this->serviceResume = new ArrayCollection();
        $this->appointmentResume = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOperatorData(): ?User
    {
        return $this->operatorData;
    }

    public function setOperatorData(User $operatorData): self
    {
        $this->operatorData = $operatorData;

        return $this;
    }

    /**
     * @return Collection|Appointment[]
     */
    public function getAppointments(): ?Collection
    {
         return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->contains($appointment)) {
            $this->appointments->removeElement($appointment);
        }

        return $this;
    }

    /**
     * @return Collection|AppointmentResume[]
     */
    public function getAppointmentResume(): Collection
    {
        return $this->appointmentResume;
    }

    public function addAppointmentResume(AppointmentResume $appointmentResume): self
    {
        if (!$this->appointmentResume->contains($appointmentResume)) {
            $this->appointmentResume[] = $appointmentResume;
            $appointmentResume->setOperator($this);
        }

        return $this;
    }

    public function removeAppointmentResume(AppointmentResume $appointmentResume): self
    {
        if ($this->appointmentResume->contains($appointmentResume)) {
            $this->appointmentResume->removeElement($appointmentResume);
            // set the owning side to null (unless already changed)
            if ($appointmentResume->getOperator() === $this) {
                $appointmentResume->setOperator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Service[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
        }

        return $this;
    }

    /**
     * @return Collection|ServiceResume[]
     */
    public function getServiceResume(): Collection
    {
        return $this->serviceResume;
    }

    public function addServiceResume(ServiceResume $serviceResume): self
    {
        if (!$this->serviceResume->contains($serviceResume)) {
            $this->serviceResume[] = $serviceResume;
            $serviceResume->setOperator($this);
        }

        return $this;
    }

    public function removeServiceResume(ServiceResume $serviceResume): self
    {
        if ($this->serviceResume->contains($serviceResume)) {
            $this->serviceResume->removeElement($serviceResume);
            // set the owning side to null (unless already changed)
            if ($serviceResume->getOperator() === $this) {
                $serviceResume->setOperator(null);
            }
        }

        return $this;
    }

    
}
