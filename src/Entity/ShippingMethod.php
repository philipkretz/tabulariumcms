<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ShippingMethod
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: "integer")]
    private int $deliveryDays = 0;
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $deliveryTime = null;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Media $logo = null;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $requiresStoreSelection = false;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedCountries = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedPostcodes = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $minPrice = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2, nullable: true)]
    private ?string $maxPrice = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $allowedCategories = null;

    #[ORM\Column(type: "integer")]
    private int $sortOrder = 0;

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: "shippingMethods")]
    private Collection $articles;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct() { $this->articles = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getPrice(): ?string { return $this->price; }
    public function setPrice(string $price): static { $this->price = $price; return $this; }
    public function getDeliveryDays(): int { return $this->deliveryDays; }
    public function setDeliveryDays(int $deliveryDays): static { $this->deliveryDays = $deliveryDays; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
public function isRequiresStoreSelection(): bool    {        return $this->requiresStoreSelection;    }    public function setRequiresStoreSelection(bool $requiresStoreSelection): static    {        $this->requiresStoreSelection = $requiresStoreSelection;        return $this;    }
    public function getAllowedCountries(): ?array { return $this->allowedCountries; }
    public function setAllowedCountries(?array $allowedCountries): static { $this->allowedCountries = $allowedCountries; return $this; }
    public function getAllowedPostcodes(): ?array { return $this->allowedPostcodes; }
    public function setAllowedPostcodes(?array $allowedPostcodes): static { $this->allowedPostcodes = $allowedPostcodes; return $this; }
    public function getMinPrice(): ?string { return $this->minPrice; }
    public function setMinPrice(?string $minPrice): static { $this->minPrice = $minPrice; return $this; }
    public function getMaxPrice(): ?string { return $this->maxPrice; }
    public function setMaxPrice(?string $maxPrice): static { $this->maxPrice = $maxPrice; return $this; }
    public function getAllowedCategories(): ?array { return $this->allowedCategories; }
    public function setAllowedCategories(?array $allowedCategories): static { $this->allowedCategories = $allowedCategories; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getDeliveryTime(): ?string { return $this->deliveryTime; }
    public function setDeliveryTime(?string $deliveryTime): static { $this->deliveryTime = $deliveryTime; return $this; }
    public function getLogo(): ?Media { return $this->logo; }
    public function setLogo(?Media $logo): static { $this->logo = $logo; return $this; }
    public function setSortOrder(int $sortOrder): static { $this->sortOrder = $sortOrder; return $this; }
    public function getArticles(): Collection { return $this->articles; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }
    public function __toString(): string { return $this->name ?? ""; }
}
