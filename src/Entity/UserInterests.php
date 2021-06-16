<?php

namespace App\Entity;

use App\Repository\UserInterestsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserInterestsRepository::class)
 */
class UserInterests
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $positions = [];

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $driveCarnet;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasCar;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasMotorcycle;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $schedule;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $workTipe = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $serviceWorker;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $disability;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $disabilityLevel;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $incorporate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPositions(): ?array
    {
        return $this->positions;
    }

    public function setPositions(?array $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    public function getDriveCarnet(): ?string
    {
        return $this->driveCarnet;
    }

    public function setDriveCarnet(?string $driveCarnet): self
    {
        $this->driveCarnet = $driveCarnet;

        return $this;
    }

    public function getHasCar(): ?bool
    {
        return $this->hasCar;
    }

    public function setHasCar(?bool $hasCar): self
    {
        $this->hasCar = $hasCar;

        return $this;
    }

    public function getHasMotorcycle(): ?bool
    {
        return $this->hasMotorcycle;
    }

    public function setHasMotorcycle(?bool $hasMotorcycle): self
    {
        $this->hasMotorcycle = $hasMotorcycle;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getWorkTipe(): ?array
    {
        return $this->workTipe;
    }

    public function setWorkTipe(?array $workTipe): self
    {
        $this->workTipe = $workTipe;

        return $this;
    }

    public function getServiceWorker(): ?bool
    {
        return $this->serviceWorker;
    }

    public function setServiceWorker(?bool $serviceWorker): self
    {
        $this->serviceWorker = $serviceWorker;

        return $this;
    }

    public function getDisability(): ?string
    {
        return $this->disability;
    }

    public function setDisability(?string $disability): self
    {
        $this->disability = $disability;

        return $this;
    }

    public function getDisabilityLevel(): ?int
    {
        return $this->disabilityLevel;
    }

    public function setDisabilityLevel(?int $disabilityLevel): self
    {
        $this->disabilityLevel = $disabilityLevel;

        return $this;
    }

    public function getIncorporate(): ?string
    {
        return $this->incorporate;
    }

    public function setIncorporate(?string $incorporate): self
    {
        $this->incorporate = $incorporate;

        return $this;
    }
}
