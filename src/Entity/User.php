<?php

namespace App\Entity;

use App\Entity\Drone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Length as ConstraintsLength;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(
    fields:['email'],
    message:"Un autre utilisateur s'est déjà inscrit avec cette adresse mail."
)]
#[UniqueEntity(
    fields:['nickname'],
    message:"Ce pseudo est déjà utilisé."
)]

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable:false)]
    private ?string $email ;

    #[ORM\Column]
    private array $roles = [];

    #[ConstraintsLength(min:8, minMessage:"Le mot de passe doit contenir 8 caractères minimum.")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $password;

    #[ConstraintsLength(min:4, minMessage:"Votre pseudo doit contenir 4 caractères minimum.")]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $nickname;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $banner;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable:false)]
    private ?\DateTimeInterface $created_at;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zip = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $addressComplement = null;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Drone $drone = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Video::class)]
    private Collection $videos;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class, orphanRemoval: true)]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Likes::class, orphanRemoval: true)]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\ManyToMany(targetEntity: Counter::class, mappedBy: 'user')]
    private Collection $counters;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AlertComment::class)]
    private Collection $alertComments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tiktok = null;

    //En cas d'appel de l'utilisateur via les articles, on passera par le pseudo
    public function __toString()
    {
        return $this->getNickname();
    }

    //mise en place de la date de création de l'utilisateur
    public function __construct()
    {
        $this->created_at = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->videos = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        //mise en place d'un avatar et une bannière par défaut à la création de l'user
        $this->banner = 'bannerDefault.png';
        $this->avatar = 'avatarDefault.jpg';
        $this->counters = new ArrayCollection();
        $this->alertComments = new ArrayCollection();
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * L'identifiant de l'user
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Le role "user" sera automatiquement donné aux nouveau utilisateurs
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getBanner(): ?string
    {
        return $this->banner;
    }

    public function setBanner(?string $banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddressComplement(): ?string
    {
        return $this->addressComplement;
    }

    public function setAddressComplement(?string $addressComplement): self
    {
        $this->addressComplement = $addressComplement;

        return $this;
    }

    public function getDrone(): ?Drone
    {
        return $this->drone;
    }

    public function setDrone(?Drone $drone): self
    {
        $this->drone = $drone;

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setUser($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getUser() === $this) {
                $video->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setAuthor($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getAuthor() === $this) {
                $article->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Likes>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Likes $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(Likes $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Counter>
     */
    public function getCounters(): Collection
    {
        return $this->counters;
    }

    public function addCounter(Counter $counter): self
    {
        if (!$this->counters->contains($counter)) {
            $this->counters->add($counter);
            $counter->addUser($this);
        }

        return $this;
    }

    public function removeCounter(Counter $counter): self
    {
        if ($this->counters->removeElement($counter)) {
            $counter->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, AlertComment>
     */
    public function getAlertComments(): Collection
    {
        return $this->alertComments;
    }

    public function addAlertComment(AlertComment $alertComment): self
    {
        if (!$this->alertComments->contains($alertComment)) {
            $this->alertComments->add($alertComment);
            $alertComment->setUser($this);
        }

        return $this;
    }

    public function removeAlertComment(AlertComment $alertComment): self
    {
        if ($this->alertComments->removeElement($alertComment)) {
            // set the owning side to null (unless already changed)
            if ($alertComment->getUser() === $this) {
                $alertComment->setUser(null);
            }
        }

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getTiktok(): ?string
    {
        return $this->tiktok;
    }

    public function setTiktok(?string $tiktok): self
    {
        $this->tiktok = $tiktok;

        return $this;
    }


    
}
