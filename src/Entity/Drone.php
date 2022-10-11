<?php

namespace App\Entity;

use App\Repository\DroneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DroneRepository::class)]
class Drone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $frame = null;

    #[ORM\Column(length: 255)]
    private ?string $motors = null;

    #[ORM\Column(length: 255)]
    private ?string $fc = null;

    #[ORM\Column(length: 255)]
    private ?string $esc = null;

    #[ORM\Column(length: 255)]
    private ?string $cam = null;

    #[ORM\Column(length: 255)]
    private ?string $reception = null;

    #[ORM\Column]
    private ?int $lipoCells = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'myDrone')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrame(): ?string
    {
        return $this->frame;
    }

    public function setFrame(string $frame): self
    {
        $this->Frame = $frame;

        return $this;
    }

    public function getMotors(): ?string
    {
        return $this->motors;
    }

    public function setMotors(string $motors): self
    {
        $this->motors = $motors;

        return $this;
    }

    public function getFc(): ?string
    {
        return $this->fc;
    }

    public function setFc(string $fc): self
    {
        $this->fc = $fc;

        return $this;
    }

    public function getEsc(): ?string
    {
        return $this->esc;
    }

    public function setEsc(string $esc): self
    {
        $this->esc = $esc;

        return $this;
    }

    public function getCam(): ?string
    {
        return $this->cam;
    }

    public function setCam(string $cam): self
    {
        $this->cam = $cam;

        return $this;
    }

    public function getReception(): ?string
    {
        return $this->reception;
    }

    public function setReception(string $reception): self
    {
        $this->reception = $reception;

        return $this;
    }

    public function getLipoCells(): ?int
    {
        return $this->lipoCells;
    }

    public function setLipoCells(int $lipoCells): self
    {
        $this->lipoCells = $lipoCells;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
