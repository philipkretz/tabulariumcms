<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "bundle_products")]
#[ORM\HasLifecycleCallbacks]
class BundleProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $sku;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $specialPrice = null;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: BundleProductItem::class, mappedBy: "bundle", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isAvailableInStore(Store $store): bool
    {
        foreach ($this->items as $item) {
            if (!($item->isAvailableInStore($store))) {
                return false;
            }
        }
        return true;
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): self { $this->sku = $sku; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): self { $this->price = $price; return $this; }
    public function getSpecialPrice(): ?string { return $this->specialPrice; }
    public function setSpecialPrice(?string $specialPrice): self { $this->specialPrice = $specialPrice; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }
    public function getItems(): Collection { return $this->items; }
    
    public function addItem(BundleProductItem $item): self
    {
        if (!($this->items->contains($item))) {
            $this->items->add($item);
            $item->setBundle($this);
        }
        return $this;
    }

    public function removeItem(BundleProductItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getBundle() === $this) {
                $item->setBundle(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string
    {
        return $this->name ?? "Bundle";
    }
}

#[ORM\Entity]
#[ORM\Table(name: "bundle_product_items")]
class BundleProductItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: BundleProduct::class, inversedBy: "items")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?BundleProduct $bundle = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Article $article;

    #[ORM\Column(type: "integer")]
    private int $quantity = 1;

    public function isAvailableInStore(Store $store): bool
    {
        $stock = $this->article->getStockForStore($store);
        return $stock && $stock->getAvailableQuantity() >= $this->quantity;
    }

    public function getId(): ?int { return $this->id; }
    public function getBundle(): ?BundleProduct { return $this->bundle; }
    public function setBundle(?BundleProduct $bundle): self { $this->bundle = $bundle; return $this; }
    public function getArticle(): Article { return $this->article; }
    public function setArticle(Article $article): self { $this->article = $article; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = max(1, $quantity); return $this; }
}
