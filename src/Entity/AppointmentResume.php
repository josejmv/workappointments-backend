<?php

namespace App\Entity;

use App\Repository\AppointmentResumeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppointmentResumeRepository::class)
 */
class AppointmentResume
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Operator::class, inversedBy="appointmentResume", cascade={"persist"})
     */
    private $operator;

    /**
     * @ORM\ManyToOne(targetEntity=Appointment::class, inversedBy="appointmentResume", cascade={"persist"})
     */
    private $appointment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $personal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $firstPosition = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $interviewer = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $secondPosition = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $interviewer2 = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $expensive;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $wantWorkWithUs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstStep;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $citedForPersonal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $disabled;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function setAppointment(?Appointment $appointment): self
    {
        $this->appointment = $appointment;

        return $this;
    }

    public function getPersonal(): ?string
    {
        return $this->personal;
    }

    public function setPersonal(?string $personal): self
    {
        $this->personal = $personal;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFirstPosition(): ?array
    {
        return $this->firstPosition;
    }

    public function setFirstPosition(?array $firstPosition): self
    {
        $this->firstPosition = $firstPosition;

        return $this;
    }

    public function getInterviewer(): ?array
    {
        return $this->interviewer;
    }

    public function setInterviewer(?array $interviewer): self
    {
        $this->interviewer = $interviewer;

        return $this;
    }

    public function getSecondPosition(): ?array
    {
        return $this->secondPosition;
    }

    public function setSecondPosition(?array $secondPosition): self
    {
        $this->secondPosition = $secondPosition;

        return $this;
    }

    public function getInterviewer2(): ?array
    {
        return $this->interviewer2;
    }

    public function setInterviewer2(?array $interviewer2): self
    {
        $this->interviewer2 = $interviewer2;

        return $this;
    }

    public function getExpensive(): ?bool
    {
        return $this->expensive;
    }

    public function setExpensive(?bool $expensive): self
    {
        $this->expensive = $expensive;

        return $this;
    }

    public function getWantWorkWithUs(): ?string
    {
        return $this->wantWorkWithUs;
    }

    public function setWantWorkWithUs(?string $wantWorkWithUs): self
    {
        $this->wantWorkWithUs = $wantWorkWithUs;

        return $this;
    }

    public function getFirstStep(): ?string
    {
        return $this->firstStep;
    }

    public function setFirstStep(?string $firstStep): self
    {
        $this->firstStep = $firstStep;

        return $this;
    }

    public function getCitedForPersonal(): ?string
    {
        return $this->citedForPersonal;
    }

    public function setCitedForPersonal(?string $citedForPersonal): self
    {
        $this->citedForPersonal = $citedForPersonal;

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
}
