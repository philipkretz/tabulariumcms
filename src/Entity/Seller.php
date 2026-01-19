<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Seller
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $companyName = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $businessName = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $taxId = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $vatNumber = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $businessAddress = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $businessCity = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $businessPostcode = null;

    #[ORM\Column(type: "string", length: 2, nullable: true)]
    private ?string $businessCountry = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $bankName = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $bankAccountNumber = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $bankIban = null;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $bankSwift = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $substoreName = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private string $commissionRate = "10.00";

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private string $discountRate = "5.00";

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private string $resaleCommissionRate = "3.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $totalSales = "0.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private string $totalRevenue = "0.00";

    #[ORM\Column(type: "integer")]
    private int $totalOrders = 0;

    #[ORM\Column(type: "string", length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = false;

    #[ORM\Column(type: "boolean")]
    private bool $canSellProducts = false;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Media $logo = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: "seller")]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function setBusinessName(?string $businessName): static
    {
        $this->businessName = $businessName;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTaxId(): ?string
    {
        return $this->taxId;
    }

    public function setTaxId(?string $taxId): static
    {
        $this->taxId = $taxId;
        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(?string $vatNumber): static
    {
        $this->vatNumber = $vatNumber;
        return $this;
    }

    public function getBusinessAddress(): ?string
    {
        return $this->businessAddress;
    }

    public function setBusinessAddress(?string $businessAddress): static
    {
        $this->businessAddress = $businessAddress;
        return $this;
    }

    public function getBusinessCity(): ?string
    {
        return $this->businessCity;
    }

    public function setBusinessCity(?string $businessCity): static
    {
        $this->businessCity = $businessCity;
        return $this;
    }

    public function getBusinessPostcode(): ?string
    {
        return $this->businessPostcode;
    }

    public function setBusinessPostcode(?string $businessPostcode): static
    {
        $this->businessPostcode = $businessPostcode;
        return $this;
    }

    public function getBusinessCountry(): ?string
    {
        return $this->businessCountry;
    }

    public function setBusinessCountry(?string $businessCountry): static
    {
        $this->businessCountry = $businessCountry;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;
        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): static
    {
        $this->bankName = $bankName;
        return $this;
    }

    public function getBankAccountNumber(): ?string
    {
        return $this->bankAccountNumber;
    }

    public function setBankAccountNumber(?string $bankAccountNumber): static
    {
        $this->bankAccountNumber = $bankAccountNumber;
        return $this;
    }

    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    public function setBankIban(?string $bankIban): static
    {
        $this->bankIban = $bankIban;
        return $this;
    }

    public function getBankSwift(): ?string
    {
        return $this->bankSwift;
    }

    public function setBankSwift(?string $bankSwift): static
    {
        $this->bankSwift = $bankSwift;
        return $this;
    }

    public function getSubstoreName(): ?string
    {
        return $this->substoreName;
    }

    public function setSubstoreName(?string $substoreName): static
    {
        $this->substoreName = $substoreName;
        return $this;
    }

    public function getCommissionRate(): string
    {
        return $this->commissionRate;
    }

    public function setCommissionRate(string $commissionRate): static
    {
        $this->commissionRate = $commissionRate;
        return $this;
    }

    public function getDiscountRate(): string
    {
        return $this->discountRate;
    }

    public function setDiscountRate(string $discountRate): static
    {
        $this->discountRate = $discountRate;
        return $this;
    }

    public function getResaleCommissionRate(): string
    {
        return $this->resaleCommissionRate;
    }

    public function setResaleCommissionRate(string $resaleCommissionRate): static
    {
        $this->resaleCommissionRate = $resaleCommissionRate;
        return $this;
    }

    public function getTotalSales(): string
    {
        return $this->totalSales;
    }

    public function setTotalSales(string $totalSales): static
    {
        $this->totalSales = $totalSales;
        return $this;
    }

    public function getTotalRevenue(): string
    {
        return $this->totalRevenue;
    }

    public function setTotalRevenue(string $totalRevenue): static
    {
        $this->totalRevenue = $totalRevenue;
        return $this;
    }

    public function getTotalOrders(): int
    {
        return $this->totalOrders;
    }

    public function setTotalOrders(int $totalOrders): static
    {
        $this->totalOrders = $totalOrders;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function canSellProducts(): bool
    {
        return $this->canSellProducts;
    }

    public function setCanSellProducts(bool $canSellProducts): static
    {
        $this->canSellProducts = $canSellProducts;
        return $this;
    }

    public function getLogo(): ?Media
    {
        return $this->logo;
    }

    public function setLogo(?Media $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function setRejectionReason(?string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

    public function getArticles(): Collection
    {
        return $this->articles;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->registeredAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->companyName ?? 'Seller';
    }
}
