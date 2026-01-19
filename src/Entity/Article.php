<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Article
{
    public const TYPE_PHYSICAL = "physical";
    public const TYPE_BUNDLE = "bundle";
    public const TYPE_VIRTUAL = "virtual";
    public const TYPE_ROOM = "room";
    public const TYPE_TIMESLOT = "timeslot";
    public const TYPE_TICKET = "ticket";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $slug = null;

    #[ORM\Column(type: "string", length: 50)]
    private string $type = self::TYPE_PHYSICAL;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank]
    private ?string $netPrice = null;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2)]
    private string $taxRate = "21.00";

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private ?string $grossPrice = null;

    #[ORM\Column(type: "integer")]
    private int $stock = 0;
    
    #[ORM\Column(type: "boolean")]
    private bool $ignoreStock = false;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $size = null;

    #[ORM\Column(type: "decimal", precision: 10, scale: 3, nullable: true)]
    private ?string $weight = null;
    
    #[ORM\Column(type: "boolean")]
    private bool $isDangerousGoods = false;

    #[ORM\Column(type: "boolean")]
    private bool $isOversizePackage = false;

    #[ORM\Column(type: "boolean")]
    private bool $requiresSpecialDelivery = false;

    #[ORM\Column(type: "integer")]
    private int $packageAmount = 1;

    #[ORM\Column(type: "string", length: 50)]
    private string $sku = "";

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\Column(type: "boolean")]
    private bool $isFeatured = false;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $mainImage = null;

    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: "article_images")]
    private Collection $images;

    #[ORM\ManyToMany(targetEntity: Media::class)]
    #[ORM\JoinTable(name: "article_videos")]
    private Collection $videos;

    #[ORM\ManyToOne(targetEntity: Media::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $downloadFile = null;

    #[ORM\ManyToMany(targetEntity: Article::class)]
    #[ORM\JoinTable(name: "article_bundle_items",
        joinColumns: [new ORM\JoinColumn(name: "bundle_id", referencedColumnName: "id")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "article_id", referencedColumnName: "id")]
    )]
    private Collection $bundleItems;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: Page::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Page $categoryPage = null;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Language $language = null;

    #[ORM\ManyToMany(targetEntity: ShippingMethod::class, inversedBy: "articles")]
    #[ORM\JoinTable(name: "article_shipping_methods")]
    private Collection $shippingMethods;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $metaDescription = null;

    #[ORM\OneToMany(targetEntity: ArticleTranslation::class, mappedBy: "article", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $translations;
    
    #[ORM\OneToMany(targetEntity: ArticleVariant::class, mappedBy: "article", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $variants;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: "articles")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Seller $seller = null;

    #[ORM\Column(type: "boolean")]
    private bool $isRequestOnly = false;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $requestEmail = null;

    #[ORM\Column(type: "boolean")]
    private bool $allowComments = true;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->shippingMethods = new ArrayCollection();
        $this->bundleItems = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->variants = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getSlug(): ?string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function isPhysical(): bool { return $this->type === self::TYPE_PHYSICAL; }
    public function isBundle(): bool { return $this->type === self::TYPE_BUNDLE; }
    public function isVirtual(): bool { return $this->type === self::TYPE_VIRTUAL; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getShortDescription(): ?string { return $this->shortDescription; }
    public function setShortDescription(?string $shortDescription): static { $this->shortDescription = $shortDescription; return $this; }

    public function getNetPrice(): ?string { return $this->netPrice; }
    public function setNetPrice(string $netPrice): static { $this->netPrice = $netPrice; $this->calculateGrossPrice(); return $this; }
    public function getTaxRate(): string { return $this->taxRate; }
    public function setTaxRate(string $taxRate): static { $this->taxRate = $taxRate; $this->calculateGrossPrice(); return $this; }
    public function getGrossPrice(): ?string { return $this->grossPrice; }

    private function calculateGrossPrice(): void
    {
        if ($this->netPrice && $this->taxRate) {
            $net = (float)$this->netPrice;
            $tax = (float)$this->taxRate;
            $this->grossPrice = (string)($net * (1 + $tax / 100));
        }
    }

    public function getStock(): int { return $this->stock; }
    public function setStock(int $stock): static { $this->stock = $stock; return $this; }
    public function getSku(): string { return $this->sku; }
    public function setSku(string $sku): static { $this->sku = $sku; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }
    public function isFeatured(): bool { return $this->isFeatured; }
    public function setIsFeatured(bool $isFeatured): static { $this->isFeatured = $isFeatured; return $this; }

    public function getMainImage(): ?Media { return $this->mainImage; }
    public function setMainImage(?Media $mainImage): static { $this->mainImage = $mainImage; return $this; }
    public function getImages(): Collection { return $this->images; }
    public function addImage(Media $image): static { if (!$this->images->contains($image)) { $this->images->add($image); } return $this; }
    public function removeImage(Media $image): static { $this->images->removeElement($image); return $this; }
    public function getVideos(): Collection { return $this->videos; }
    public function addVideo(Media $video): static { if (!$this->videos->contains($video)) { $this->videos->add($video); } return $this; }
    public function removeVideo(Media $video): static { $this->videos->removeElement($video); return $this; }

    public function getDownloadFile(): ?Media { return $this->downloadFile; }
    public function setDownloadFile(?Media $downloadFile): static { $this->downloadFile = $downloadFile; return $this; }

    public function getBundleItems(): Collection { return $this->bundleItems; }
    public function addBundleItem(Article $article): static { if (!$this->bundleItems->contains($article)) { $this->bundleItems->add($article); } return $this; }
    public function removeBundleItem(Article $article): static { $this->bundleItems->removeElement($article); return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }
    public function getCategoryPage(): ?Page { return $this->categoryPage; }
    public function setCategoryPage(?Page $categoryPage): static { $this->categoryPage = $categoryPage; return $this; }
    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): static { $this->language = $language; return $this; }

    public function getShippingMethods(): Collection { return $this->shippingMethods; }
    public function addShippingMethod(ShippingMethod $shippingMethod): static { if (!$this->shippingMethods->contains($shippingMethod)) { $this->shippingMethods->add($shippingMethod); } return $this; }
    public function removeShippingMethod(ShippingMethod $shippingMethod): static { $this->shippingMethods->removeElement($shippingMethod); return $this; }

    public function getMetaTitle(): ?string { return $this->metaTitle; }
    
    public function getIgnoreStock(): bool { return $this->ignoreStock; }
    public function setIgnoreStock(bool $ignoreStock): static { $this->ignoreStock = $ignoreStock; return $this; }
    public function getSize(): ?string { return $this->size; }
    public function setSize(?string $size): static { $this->size = $size; return $this; }
    public function getWeight(): ?string { return $this->weight; }
    public function getIsDangerousGoods(): bool { return $this->isDangerousGoods; }
    public function setIsDangerousGoods(bool $isDangerousGoods): static { $this->isDangerousGoods = $isDangerousGoods; return $this; }
    public function getIsOversizePackage(): bool { return $this->isOversizePackage; }
    public function setIsOversizePackage(bool $isOversizePackage): static { $this->isOversizePackage = $isOversizePackage; return $this; }
    public function getRequiresSpecialDelivery(): bool { return $this->requiresSpecialDelivery; }
    public function setRequiresSpecialDelivery(bool $requiresSpecialDelivery): static { $this->requiresSpecialDelivery = $requiresSpecialDelivery; return $this; }
    public function getPackageAmount(): int { return $this->packageAmount; }
    public function setPackageAmount(int $packageAmount): static { $this->packageAmount = $packageAmount; return $this; }
    public function setWeight(?string $weight): static { $this->weight = $weight; return $this; }
    public function setMetaTitle(?string $metaTitle): static { $this->metaTitle = $metaTitle; return $this; }
    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $metaDescription): static { $this->metaDescription = $metaDescription; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getSeller(): ?Seller { return $this->seller; }
    public function setSeller(?Seller $seller): static { $this->seller = $seller; return $this; }

    public function isRequestOnly(): bool { return $this->isRequestOnly; }
    public function setIsRequestOnly(bool $isRequestOnly): static { $this->isRequestOnly = $isRequestOnly; return $this; }

    public function getRequestEmail(): ?string { return $this->requestEmail; }
    public function setRequestEmail(?string $requestEmail): static { $this->requestEmail = $requestEmail; return $this; }

    
    public function getTranslations(): Collection { return $this->translations; }
    public function addTranslation(ArticleTranslation $translation): static { if (!$this->translations->contains($translation)) { $this->translations->add($translation); $translation->setArticle($this); } return $this; }
    public function removeTranslation(ArticleTranslation $translation): static { if ($this->translations->removeElement($translation)) { if ($translation->getArticle() === $this) { $translation->setArticle(null); } } return $this; }
    
    public function getVariants(): Collection { return $this->variants; }
    public function addVariant(ArticleVariant $variant): static { if (!$this->variants->contains($variant)) { $this->variants->add($variant); $variant->setArticle($this); } return $this; }
    public function removeVariant(ArticleVariant $variant): static { if ($this->variants->removeElement($variant)) { if ($variant->getArticle() === $this) { $variant->setArticle(null); } } return $this; }

    public function isAllowComments(): bool { return $this->allowComments; }
    public function setAllowComments(bool $allowComments): static { $this->allowComments = $allowComments; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->calculateGrossPrice();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->calculateGrossPrice();
    }

    public function __toString(): string { return $this->name ?? ""; }
}
