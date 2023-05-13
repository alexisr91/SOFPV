<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VideoRepository;


#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    private ?int $views = null;

    #[ORM\Column(length: 255)]
    private ?string $source = null;

    #[ORM\Column(length: 255)]
    private ?string $thumbnail = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\OneToOne(mappedBy: 'video', cascade: ['persist', 'remove'])]
    private ?Article $article = null;

    #[ORM\Column]
    private ?bool $isUploaded = null;

    public function __construct()
    {
        $this->views = 0;
        $this->active = 1;
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->thumbnail = 'blogDefault.png';
        $this->duration = '00:00';

    }

    //Conversion de l'URL fourni par l'user en URL lisible avec Youtube (embed)
    public function convertYT($videoURL){
            // "https://www.youtube.com/watch?v=Ojs5cERnQqg"
            // 'https://www.youtube.com/embed/Ojs5cERnQqg';
            // Difference entre  watch?v= et embed/ 
        $convertedURL = str_replace("watch?v=","embed/",$videoURL);
        //suppression de la partie concernant le channel Youtube (https://www.youtube.com/xxxxxxxxxxxx&ab_channel=LofiGirl)
        $convertedURL = strtok($convertedURL, "&");
        
        return $convertedURL;
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

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;

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

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

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

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        // unset the owning side of the relation if necessary
        if ($article === null && $this->article !== null) {
            $this->article->setVideo(null);
        }

        // set the owning side of the relation if necessary
        if ($article !== null && $article->getVideo() !== $this) {
            $article->setVideo($this);
        }

        $this->article = $article;

        return $this;
    }

    public function isIsUploaded(): ?bool
    {
        return $this->isUploaded;
    }

    public function setIsUploaded(bool $isUploaded): self
    {
        $this->isUploaded = $isUploaded;

        return $this;
    }
}
