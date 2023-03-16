<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\OneToMany(mappedBy: 'comment', targetEntity: AlertComment::class)]
    private Collection $alertComments;


    public function __construct(){
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->alertComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

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
            $alertComment->setComment($this);
        }

        return $this;
    }

    public function removeAlertComment(AlertComment $alertComment): self
    {
        if ($this->alertComments->removeElement($alertComment)) {
            // set the owning side to null (unless already changed)
            if ($alertComment->getComment() === $this) {
                $alertComment->setComment(null);
            }
        }

        return $this;
    }


}
