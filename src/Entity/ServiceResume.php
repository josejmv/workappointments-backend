<?php

namespace App\Entity;

use App\Repository\ServiceResumeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServiceResumeRepository::class)
 */
class ServiceResume
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $attended;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $serviceDate;

    /**
     * @ORM\ManyToOne(targetEntity=Operator::class, inversedBy="serviceResume", cascade={"persist"})
     */
    private $operator;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="serviceResume", cascade={"persist"})
     */
    private $service;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disabled;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $workerHours;
    
    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $serviceStartHour;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $serviceEndHour;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAttended(): ?bool
    {
        return $this->attended;
    }

    public function setAttended(?bool $attended): self
    {
        $this->attended = $attended;

        return $this;
    }

    public function getServiceDate(): ?\DateTimeInterface
    {
        return $this->serviceDate;
    }

    public function setServiceDate(?\DateTimeInterface $serviceDate): self
    {
        $this->serviceDate = $serviceDate;

        return $this;
    }

    public function getOperator(): ?Operator
    {
        return $this->operator;
    }

    public function setOperator(?Operator $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getWorkerHours(): ?\DateTimeInterface
    {
        return $this->workerHours;
    }

    public function setWorkerHours(?\DateTimeInterface $workerHours): self
    {
        $this->workerHours = $workerHours;

        return $this;
    }
    
    public function getServiceStartHour(): ?\DateTimeInterface
    {
        return $this->serviceStartHour;
    }

    public function setServiceStartHour(?\DateTimeInterface $serviceStartHour): self
    {
        $this->serviceStartHour = $serviceStartHour;

        return $this;
    }

    public function getServiceEndHour(): ?\DateTimeInterface
    {
        return $this->serviceEndHour;
    }

    public function setServiceEndHour(?\DateTimeInterface $serviceEndHour): self
    {
        $this->serviceEndHour = $serviceEndHour;

        return $this;
    }
}
