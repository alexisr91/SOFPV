<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    const DEVISE = 'eur';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    private ?Order $ordering = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $this->quantity = 0;
        $this->amount = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
   

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(Product $product, $quantity): self
    {
        $this->amount = ($product->getPriceTTC()*$quantity);

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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }


    public function getOrdering(): ?Order
    {
        return $this->ordering;
    }

    public function setOrdering(?Order $ordering): self
    {
        $this->ordering = $ordering;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

}
