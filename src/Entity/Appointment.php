<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppointmentsRepository::class)
 */
class Appointment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     */
    private $admin;

    /**
     * @ORM\ManyToOne(targetEntity=Manager::class)
     */
    private $manager;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $direction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $hour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quantityEmployees;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $tipeWork = [];

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $active;

    /**
     * @ORM\Column(type="string", length=900, nullable=true)
     */
    private $newCreated;

    /**
     * @ORM\ManyToMany(targetEntity=Operator::class, mappedBy="appointments", cascade={"persist", "remove"})
     */
    private $operators;

    /**
     * @ORM\OneToMany(targetEntity=AppointmentResume::class, mappedBy="appointment", cascade={"persist", "remove"})
     */
    private $appointmentResume;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
        $this->appointmentResume = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function setManager(?Manager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDirection(): ?string
    {
        return $this->direction;
    }

    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHour(): ?\DateTimeInterface
    {
        return $this->hour;
    }

    public function setHour(?\DateTimeInterface $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getQuantityEmployees(): ?int
    {
        return $this->quantityEmployees;
    }

    public function setQuantityEmployees(?int $quantityEmployees): self
    {
        $this->quantityEmployees = $quantityEmployees;

        return $this;
    }

    public function getTipeWork(): ?array
    {
        return $this->tipeWork;
    }

    public function setTipeWork(?array $tipeWork): self
    {
        $this->tipeWork = $tipeWork;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active = true): self
    {
        $this->active = $active;

        return $this;
    }

    public function getNewCreated(): ?string
    {
        return $this->newCreated;
    }

    public function setNewCreated(?string $newCreated): self
    {
        $this->newCreated = $newCreated;

        return $this;
    }

    /**
     * @return Collection|Operator[]
     */
    public function getOperators(): Collection
    {
        return $this->operators;
    }

    public function addOperator(Operator $operator): self
    {
        if (!$this->operators->contains($operator)) {
            $this->operators[] = $operator;
            $operator->addAppointment($this);
        }

        return $this;
    }

    public function removeOperator(Operator $operator): self
    {
        if ($this->operators->contains($operator)) {
            $this->operators->removeElement($operator);
            $operator->removeAppointment($this);
        }

        return $this;
    }

    public function removeAllOperator(): self
    {
        $operators = $this->getOperators();
        foreach ($operators as $operator) {
            $this->operators->removeElement($operator);
            if ($operator->getAppointment() === $this) {
                $operator->setAppointment(null);
            }
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
            $appointmentResume->setAppointment($this);
        }

        return $this;
    }

    public function removeAppointmentResume(AppointmentResume $appointmentResume): self
    {
        if ($this->appointmentResume->contains($appointmentResume)) {
            $this->appointmentResume->removeElement($appointmentResume);
            // set the owning side to null (unless already changed)
            if ($appointmentResume->getAppointment() === $this) {
                $appointmentResume->setAppointment(null);
            }
        }

        return $this;
    }
}
