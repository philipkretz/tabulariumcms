<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Article $article = null;

    #[ORM\Column(type: "integer")]
    #[Assert\Positive]
    private int $quantity = 1;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $unitPrice = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $taxRate = "21.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $subtotal = "0.00";

    // Store article details at time of purchase (in case article is later modified/deleted)
    #[ORM\Column(type: "string", length: 255)]
    private ?string $articleName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $articleSku = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): static
    {
        $this->article = $article;
        
        // Auto-populate article details
        $this->articleName = $article->getName();
        $this->articleSku = $article->getSku();
        $this->unitPrice = $article->getGrossPrice();
        $this->taxRate = $article->getTaxRate();
        
        $this->calculateSubtotal();
        
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->calculateSubtotal();
        return $this;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;
        $this->calculateSubtotal();
        return $this;
    }

    public function getTaxRate(): string
    {
        return $this->taxRate;
    }

    public function setTaxRate(string $taxRate): static
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function getSubtotal(): string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;
        return $this;
    }

    public function getArticleName(): ?string
    {
        return $this->articleName;
    }

    public function setArticleName(string $articleName): static
    {
        $this->articleName = $articleName;
        return $this;
    }

    public function getArticleSku(): ?string
    {
        return $this->articleSku;
    }

    public function setArticleSku(?string $articleSku): static
    {
        $this->articleSku = $articleSku;
        return $this;
    }

    private function calculateSubtotal(): void
    {
        $price = (float)$this->unitPrice;
        $qty = (int)$this->quantity;
        $this->subtotal = (string)($price * $qty);
    }

    public function __toString(): string
    {
        return $this->articleName ?? "Order Item #" . $this->id;
    }
}
