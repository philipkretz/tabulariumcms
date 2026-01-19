<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Cart $cart = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: ArticleVariant::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?ArticleVariant $variant = null;

    #[ORM\Column(type: "integer")]
    private int $quantity = 1;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $price = null;

    public function getId(): ?int { return $this->id; }

    public function getCart(): ?Cart { return $this->cart; }
    public function setCart(?Cart $cart): static { $this->cart = $cart; return $this; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getVariant(): ?ArticleVariant { return $this->variant; }
    public function setVariant(?ArticleVariant $variant): static { $this->variant = $variant; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): static { $this->quantity = $quantity; return $this; }

    public function getPrice(): ?string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }

    public function getSubtotal(): string
    {
        return number_format((float)$this->price * $this->quantity, 2, '.', '');
    }

    public function getDisplayName(): string
    {
        $name = $this->article?->getName() ?? 'Product';
        if ($this->variant) {
            $name .= ' - ' . (string)$this->variant;
        }
        return $name;
    }
}
