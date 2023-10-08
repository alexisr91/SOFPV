<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Valid;

#[UniqueEntity(
    fields: 'title',
    message: 'Un autre article possède déjà ce titre, veuillez en changer !'
)]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[Valid()]
    #[ORM\OneToOne(inversedBy: 'article', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Video $video = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[Valid()]
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Image::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?Collection $images = null;

    #[ORM\Column]
    private ?int $views = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Likes::class, orphanRemoval: true)]
    private ?Collection $likes = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, orphanRemoval: true)]
    private ?Collection $comments = null;

    #[ORM\ManyToOne(inversedBy: 'articles', cascade: ['persist'])]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $adminNews = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Alert::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Collection $alerts = null;

    #[ORM\Column]
    private ?int $views = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Likes::class, orphanRemoval: true)]
    private ?Collection $likes = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, orphanRemoval:true)]
    private ?Collection $comments = null;

    #[ORM\ManyToOne(inversedBy: 'articles', cascade:['persist'])]
    private ?Category $category = null;

    #[ORM\Column]
    private ?bool $adminNews = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Alert::class, cascade: ['persist', 'remove'], orphanRemoval:true)]
    private Collection $alerts;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));   
        $this->images = new ArrayCollection();
        $this->views = 0;
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->adminNews = false;
        $this->active = true;
        $this->alerts = new ArrayCollection();
    }

    // création du slug et mise à jour si le titre est modifié par l'auteur
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initSlug()
    {
        if (empty($this->slug) || $this->slug != $this->title) {
            $slugger = new Slugify();
            $this->slug = $slugger->slugify($this->title);
        }
        return $this->slug;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): void
    {
        if (!$this->images->contains($image)) {
            $image->setArticle($this);
            $this->images->add($image);
        }
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getArticle() === $this) {
                $image->setArticle(null);
            }
        }

        return $this;
    }

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

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
            $like->setArticle($this);
        }

        return $this;
    }

    public function removeLike(Likes $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getArticle() === $this) {
                $like->setArticle(null);
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
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isAdminNews(): ?bool
    {
        return $this->adminNews;
    }

    public function setAdminNews(bool $adminNews): self
    {
        $this->adminNews = $adminNews;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Alert>
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(Alert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts->add($alert);
            $alert->setArticle($this);
        }

        return $this;
    }

    public function removeAlert(Alert $alert): self
    {
        if ($this->alerts->removeElement($alert)) {
            // set the owning side to null (unless already changed)
            if ($alert->getArticle() === $this) {
                $alert->setArticle(null);
            }
        }

        return $this;
    }
}
