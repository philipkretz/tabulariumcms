<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class VoucherCode
{
    public const TYPE_PERCENTAGE = "percentage";
    public const TYPE_FIXED = "fixed";

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $code = null;
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "integer")]
    private int $sortOrder = 0;

    #[ORM\Column(length: 50)]
    private string $type = self::TYPE_PERCENTAGE;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $value = null;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $validUntil = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $maxUses = null;

    #[ORM\Column(type: "integer")]
    private int $usedCount = 0;

    #[ORM\Column(type: "boolean")]
    private bool $isOneTime = false;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedCategories = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedArticles = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $minOrderValue = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }
    public function getCode(): ?string { return $this->code; }
    public function setCode(string $code): static { $this->code = strtoupper($code); return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getValue(): ?string { return $this->value; }
    public function setValue(string $value): static { $this->value = $value; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function getValidFrom(): ?\DateTimeImmutable { return $this->validFrom; }
    public function setValidFrom(?\DateTimeImmutable $validFrom): static { $this->validFrom = $validFrom; return $this; }
    public function getValidUntil(): ?\DateTimeImmutable { return $this->validUntil; }
    public function setValidUntil(?\DateTimeImmutable $validUntil): static { $this->validUntil = $validUntil; return $this; }
    public function getMaxUses(): ?int { return $this->maxUses; }
    public function setMaxUses(?int $maxUses): static { $this->maxUses = $maxUses; return $this; }
    public function getUsedCount(): int { return $this->usedCount; }
    public function incrementUsedCount(): static { $this->usedCount++; return $this; }
    public function isOneTime(): bool { return $this->isOneTime; }
    public function setIsOneTime(bool $isOneTime): static { $this->isOneTime = $isOneTime; return $this; }
    public function getAllowedCategories(): ?array { return $this->allowedCategories; }
    public function setAllowedCategories(?array $allowedCategories): static { $this->allowedCategories = $allowedCategories; return $this; }
    public function getAllowedArticles(): ?array { return $this->allowedArticles; }
    public function setAllowedArticles(?array $allowedArticles): static { $this->allowedArticles = $allowedArticles; return $this; }
    public function getMinOrderValue(): ?string { return $this->minOrderValue; }
    public function setMinOrderValue(?string $minOrderValue): static { $this->minOrderValue = $minOrderValue; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }
    public function getDiscountValue(): ?string { return $this->value; }
    public function setDiscountValue(?string $value): static { $this->value = $value; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function isValid(): bool
    {
        if (!$this->isActive) return false;
        $now = new \DateTimeImmutable();
        if ($this->validFrom && $now < $this->validFrom) return false;
        if ($this->validUntil && $now > $this->validUntil) return false;
        if ($this->maxUses && $this->usedCount >= $this->maxUses) return false;
        return true;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }
    public function __toString(): string { return $this->code ?? ""; }
}
