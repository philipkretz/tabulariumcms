<?php

namespace App\Entity;

use App\Repository\ThemeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThemeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Theme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $author = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $version = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $category = 'user'; // 'default', 'user', 'premium'

    #[ORM\Column(type: 'text')]
    private ?string $thumbnailPath = null;

    #[ORM\Column(type: 'json')]
    private array $config = [];

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\Column(type: 'boolean')]
    private bool $isDefault = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'json')]
    private array $files = [];

    // Theme Customization Fields
    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $primaryColor = null;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $secondaryColor = null;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $accentColor = null;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $backgroundColor = null;

    #[ORM\Column(type: 'string', length: 7, nullable: true)]
    private ?string $textColor = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $headingFont = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $bodyFont = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $fontSize = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $sidebarPosition = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $headerStyle = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $containerWidth = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customCss = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function setThumbnailPath(?string $thumbnailPath): static
    {
        $this->thumbnailPath = $thumbnailPath;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): static
    {
        $this->files = $files;

        return $this;
    }

    public function getFile(string $path): ?array
    {
        return $this->files[$path] ?? null;
    }

    public function setFile(string $path, array $fileData): static
    {
        $this->files[$path] = $fileData;

        return $this;
    }

    public function removeFile(string $path): static
    {
        unset($this->files[$path]);

        return $this;
    }

    public function getDirectoryPath(): string
    {
        return '/themes/' . $this->name;
    }

    // Theme Customization Getters and Setters
    public function getPrimaryColor(): ?string { return $this->primaryColor; }
    public function setPrimaryColor(?string $primaryColor): static { $this->primaryColor = $primaryColor; return $this; }

    public function getSecondaryColor(): ?string { return $this->secondaryColor; }
    public function setSecondaryColor(?string $secondaryColor): static { $this->secondaryColor = $secondaryColor; return $this; }

    public function getAccentColor(): ?string { return $this->accentColor; }
    public function setAccentColor(?string $accentColor): static { $this->accentColor = $accentColor; return $this; }

    public function getBackgroundColor(): ?string { return $this->backgroundColor; }
    public function setBackgroundColor(?string $backgroundColor): static { $this->backgroundColor = $backgroundColor; return $this; }

    public function getTextColor(): ?string { return $this->textColor; }
    public function setTextColor(?string $textColor): static { $this->textColor = $textColor; return $this; }

    public function getHeadingFont(): ?string { return $this->headingFont; }
    public function setHeadingFont(?string $headingFont): static { $this->headingFont = $headingFont; return $this; }

    public function getBodyFont(): ?string { return $this->bodyFont; }
    public function setBodyFont(?string $bodyFont): static { $this->bodyFont = $bodyFont; return $this; }

    public function getFontSize(): ?string { return $this->fontSize; }
    public function setFontSize(?string $fontSize): static { $this->fontSize = $fontSize; return $this; }

    public function getSidebarPosition(): ?string { return $this->sidebarPosition; }
    public function setSidebarPosition(?string $sidebarPosition): static { $this->sidebarPosition = $sidebarPosition; return $this; }

    public function getHeaderStyle(): ?string { return $this->headerStyle; }
    public function setHeaderStyle(?string $headerStyle): static { $this->headerStyle = $headerStyle; return $this; }

    public function getContainerWidth(): ?string { return $this->containerWidth; }
    public function setContainerWidth(?string $containerWidth): static { $this->containerWidth = $containerWidth; return $this; }

    public function getCustomCss(): ?string { return $this->customCss; }
    public function setCustomCss(?string $customCss): static { $this->customCss = $customCss; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->displayName ?? $this->name ?? '';
    }
}