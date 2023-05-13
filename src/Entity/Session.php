<?php

namespace App\Entity;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\PrePersist;
use App\Repository\SessionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\PostUpdate;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[HasLifecycleCallbacks] 
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?MapSpot $mapSpot = null;

    #[Assert\GreaterThanOrEqual('today', message:"Attention: Vous ne pouvez pas ajouter de session à une date antérieure à aujourd'hui.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]    
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $timesheet = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'sessions')]
    private Collection $users;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?bool $past = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    #[Prepersist]
    #[PreUpdate]
    public function isAlreadyPast():bool{
        //date de la session
        $sessionDate = $this->date;
        //date actuelle FR 
        $now = new DateTime("now", new \DateTimeZone('Europe/Paris'));
    
       //si la date est déjà passée, on retourne false  
       if($now > $sessionDate){
           return $this->past = true;
       } else {
          return $this->past = false;
       }
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMapSpot(): ?MapSpot
    {
        return $this->mapSpot;
    }

    public function setMapSpot(?MapSpot $mapSpot): self
    {
        $this->mapSpot = $mapSpot;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTimesheet(): ?string
    {
        return $this->timesheet;
    }

    public function setTimesheet(string $timesheet): self
    {
        $this->timesheet = $timesheet;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isPast(): ?bool
    {
        return $this->past;
    }

    public function setPast(bool $past): self
    {
        $this->past = $past;

        return $this;
    }
}
