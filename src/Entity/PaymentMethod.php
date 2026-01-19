<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class PaymentMethod
{
    public const TYPE_PREPAYMENT = "prepayment";
    public const TYPE_AT_STORE = "at_store";
    public const TYPE_CASH_ON_DELIVERY = "cash_on_delivery";
    public const TYPE_STRIPE = "stripe";
    public const TYPE_PAYPAL = "paypal";
    public const TYPE_AMAZON_PAY = "amazon_pay";
    public const TYPE_KLARNA = "klarna";
    public const TYPE_ALIPAY = "alipay";
    public const TYPE_BITPAY = "bitpay";
    public const TYPE_GOOGLE_PAY = "google_pay";

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private string $type = self::TYPE_PREPAYMENT;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $fee = "0.00";

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedCountries = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $minPrice = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $maxPrice = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedCategories = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $config = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Media $logo = null;

    #[ORM\Column(type: "integer")]
    private int $sortOrder = 0;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getFee(): string { return $this->fee; }
    public function setFee(string $fee): static { $this->fee = $fee; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function getAllowedCountries(): ?array { return $this->allowedCountries; }
    public function setAllowedCountries(?array $allowedCountries): static { $this->allowedCountries = $allowedCountries; return $this; }
    public function getMinPrice(): ?string { return $this->minPrice; }
    public function setMinPrice(?string $minPrice): static { $this->minPrice = $minPrice; return $this; }
    public function getMaxPrice(): ?string { return $this->maxPrice; }
    public function setMaxPrice(?string $maxPrice): static { $this->maxPrice = $maxPrice; return $this; }
    public function getAllowedCategories(): ?array { return $this->allowedCategories; }
    public function setAllowedCategories(?array $allowedCategories): static { $this->allowedCategories = $allowedCategories; return $this; }
    public function getConfig(): ?array { return $this->config; }
    public function setConfig(?array $config): static { $this->config = $config; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getLogo(): ?Media { return $this->logo; }
    public function setLogo(?Media $logo): static { $this->logo = $logo; return $this; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }
    public function __toString(): string { return $this->name ?? ""; }
}
