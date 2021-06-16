<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 */
class Service
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Manager::class)
     */
    private $manager;

    /**
     * @ORM\ManyToOne(targetEntity=Admin::class)
     */
    private $admin;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $direction;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startHour;

    /**
     * @ORM\Column(type="datetime")
     */
    private $endHour;

    /**
     * @ORM\Column(type="datetime")
     */
    private $totalHours;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private $days;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $minEmployeeQuantity;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxEmployeeQuantity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $minHoursPerEmployees;

    /**
     * @ORM\Column(type="string", length=900, nullable=true)
     */
    private $filter;

    /**
     * @ORM\ManyToMany(targetEntity=Operator::class, mappedBy="services", cascade={"persist", "remove"})
     */
    private $operators;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity=ServiceResume::class, mappedBy="service", cascade={"persist", "remove"})
     */
    private $serviceResume;

    /**
     * @ORM\Column(type="string", length=900)
     */
    private $newCreated;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $active;

    public function __construct()
    {
        $this->operators = new ArrayCollection();
        $this->serviceResume = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setManager(?Manager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function setAdmin(?Admin $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(\DateTimeInterface $startHour): self
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(\DateTimeInterface $endHour): self
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getTotalHours(): ?\DateTimeInterface
    {
        return $this->totalHours;
    }

    public function setTotalHours(\DateTimeInterface $totalHours): self
    {
        $this->totalHours = $totalHours;

        return $this;
    }

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(int $days): self
    {
        $this->days = $days;

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

    public function getMinEmployeeQuantity(): ?int
    {
        return $this->minEmployeeQuantity;
    }

    public function setMinEmployeeQuantity(int $minEmployeeQuantity): self
    {
        $this->minEmployeeQuantity = $minEmployeeQuantity;

        return $this;
    }

    public function getMaxEmployeeQuantity(): ?int
    {
        return $this->maxEmployeeQuantity;
    }

    public function setMaxEmployeeQuantity(int $maxEmployeeQuantity): self
    {
        $this->maxEmployeeQuantity = $maxEmployeeQuantity;

        return $this;
    }

    public function getMinHoursPerEmployees(): ?\DateTimeInterface
    {
        return $this->minHoursPerEmployees;
    }

    public function setMinHoursPerEmployees(?\DateTimeInterface $minHoursPerEmployees): self
    {
        $this->minHoursPerEmployees = $minHoursPerEmployees;

        return $this;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): self
    {
        $this->filter = $filter;

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
            $operator->addService($this);
        }

        return $this;
    }

    public function removeOperator(Operator $operator): self
    {
        if ($this->operators->contains($operator)) {
            $this->operators->removeElement($operator);
            $operator->removeService($this);
        }

        return $this;
    }

    public function removeAllOperator(): self
    {
        $operators = $this->getOperators();
        foreach ($operators as $operator) {
            $this->operators->removeElement($operator);
            foreach($operator->getServices() as $service){
                if ($service === $this) {
                    $operator->getServices()->removeElement($this);
                }
            }
        }

        return $this;
    }

    public function getComments(): ?array
    {
        return $this->comments;
    }

    public function setComments(?array $comments): self
    {
        $this->comments = $comments;

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
            $serviceResume->setService($this);
        }

        return $this;
    }

    public function removeServiceResume(ServiceResume $serviceResume): self
    {
        if ($this->serviceResume->contains($serviceResume)) {
            $this->serviceResume->removeElement($serviceResume);
            // set the owning side to null (unless already changed)
            if ($serviceResume->getService() === $this) {
                $serviceResume->setService(null);
            }
        }

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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active = true): self
    {
        $this->active = $active;

        return $this;
    }
}
