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

    protected $updatedAt; 

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    public function isAlreadyPast(): bool
    {
        // date de la session
        $sessionDate = $this->date;
        // date actuelle FR sans l'heure
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        // on set l'heure a 00:00 pour permettre une session le jour même - set hour to 00:00 for accept same day session
        $now->setTime(0, 0);

        // si la date est déjà passée, on retourne false  (la publication pour le jour même est autorisé )
        if ($now <= $sessionDate) {
            return false;
        } else {
            return true;
        }
    }

    public function setUpdate(){
        $this->updatedAt = new DateTime("now", new \DateTimeZone('Europe/Paris'));
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
