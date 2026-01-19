<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "product_stocks")]
#[ORM\UniqueConstraint(name: "product_store_unique", columns: ["article_id", "store_id"])]
#[ORM\HasLifecycleCallbacks]
class ProductStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: Store::class, inversedBy: "stocks")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private Store $store;

    #[ORM\Column(type: "integer")]
    private int $quantity = 0;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $reservedQuantity = 0;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $minQuantity = 0;

    #[ORM\Column(type: "boolean")]
    private bool $trackStock = true;

    #[ORM\Column(type: "boolean")]
    private bool $allowBackorder = false;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $storePrice = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $posProductId = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $lastSyncAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    private \DateTimeImmutable $updatedAt;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getAvailableQuantity(): int
    {
        if (!($this->trackStock)) {
            return PHP_INT_MAX;
        }
        return max(0, $this->quantity - $this->reservedQuantity);
    }

    public function isInStock(): bool
    {
        if (!($this->trackStock)) {
            return true;
        }
        return $this->getAvailableQuantity() > 0 || $this->allowBackorder;
    }

    public function reserveQuantity(int $quantity): bool
    {
        if (!($this->trackStock)) {
            return true;
        }
        
        if ($this->getAvailableQuantity() >= $quantity) {
            $this->reservedQuantity += $quantity;
            return true;
        }
        
        return $this->allowBackorder;
    }

    public function releaseQuantity(int $quantity): void
    {
        $this->reservedQuantity = max(0, $this->reservedQuantity - $quantity);
    }

    public function decrementStock(int $quantity): void
    {
        if ($this->trackStock) {
            $this->quantity = max(0, $this->quantity - $quantity);
            $this->releaseQuantity($quantity);
        }
    }

    public function incrementStock(int $quantity): void
    {
        if ($this->trackStock) {
            $this->quantity += $quantity;
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getArticle(): Article { return $this->article; }
    public function setArticle(Article $article): self { $this->article = $article; return $this; }
    public function getStore(): Store { return $this->store; }
    public function setStore(Store $store): self { $this->store = $store; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = max(0, $quantity); return $this; }
    public function getReservedQuantity(): ?int { return $this->reservedQuantity; }
    public function setReservedQuantity(?int $reservedQuantity): self { $this->reservedQuantity = $reservedQuantity; return $this; }
    public function getMinQuantity(): ?int { return $this->minQuantity; }
    public function setMinQuantity(?int $minQuantity): self { $this->minQuantity = $minQuantity; return $this; }
    public function isTrackStock(): bool { return $this->trackStock; }
    public function setTrackStock(bool $trackStock): self { $this->trackStock = $trackStock; return $this; }
    public function isAllowBackorder(): bool { return $this->allowBackorder; }
    public function setAllowBackorder(bool $allowBackorder): self { $this->allowBackorder = $allowBackorder; return $this; }
    public function getStorePrice(): ?string { return $this->storePrice; }
    public function setStorePrice(?string $storePrice): self { $this->storePrice = $storePrice; return $this; }
    public function getPosProductId(): ?string { return $this->posProductId; }
    public function setPosProductId(?string $posProductId): self { $this->posProductId = $posProductId; return $this; }
    public function getLastSyncAt(): ?\DateTimeImmutable { return $this->lastSyncAt; }
    public function setLastSyncAt(?\DateTimeImmutable $lastSyncAt): self { $this->lastSyncAt = $lastSyncAt; return $this; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function __toString(): string
    {
        return sprintf("%s - %s: %d", $this->article?->getTitle() ?? "Product", $this->store?->getName() ?? "Store", $this->quantity);
    }
}
