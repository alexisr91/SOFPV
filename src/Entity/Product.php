<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $price_TTC = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column]
    #[PositiveOrZero]
    private int $stock;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?float $price_HT = null;

    public $tva = 20 / 100;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Cart::class)]
    private Collection $carts;

    #[ORM\Column]
    private ?bool $active = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->stock = 0;
        $this->price_TTC = number_format($this->price_HT + ($this->price_HT * $this->tva), 2);
        $this->carts = new ArrayCollection();
        $this->active = false;
        $this->image = 'product-default.jpg';
    }

    // vérification de la date de sortie du produit pour déterminer sa "nouveauté" par rapport aux autres produits (ajout de badge css)
    public function isNewProduct(): bool
    {
        // date de creation du produit
        $createdProductDate = $this->createdAt;
        // date actuelle FR
        $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        // date de creation + 15 jours comme limite pour déterminer un produit "nouveau"
        $limit = date_add($createdProductDate, date_interval_create_from_date_string('+15 days'));

        // si la limite est dépassée, on retourne false
        if ($now > $limit) {
            return false;
        } else {
            return true;
        }
    }

    // creation du slug et update si le nom du produit est modifié par l'admin
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initSlug()
    {
        if (empty($this->slug) || $this->slug != $this->name) {
            $slugger = new Slugify();
            $this->slug = $slugger->slugify($this->name);
        }
    }

    // modification du prix et du prix TTC
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function priceUpdate()
    {
        $this->price_TTC = number_format($this->price_HT + ($this->price_HT * $this->tva), 2);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPriceTTC(): ?float
    {
        return $this->price_TTC;
    }

    public function setPriceTTC(float $price_TTC): self
    {
        $this->price_TTC = number_format($price_TTC, 2);

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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

    public function getPriceHT(): ?float
    {
        return $this->price_HT;
    }

    public function setPriceHT(float $price_HT): self
    {
        $this->price_HT = number_format($price_HT, 2);

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setProduct($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getProduct() === $this) {
                $cart->setProduct(null);
            }
        }

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
}
