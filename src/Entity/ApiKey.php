<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'api_key')]
#[ORM\HasLifecycleCallbacks]
class ApiKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private ?string $apiKey = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rateLimit = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column(type: 'integer')]
    private int $requestCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastRequestAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $ipWhitelist = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $permissions = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTimeImmutable();
        }
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function generateApiKey(): void
    {
        $this->apiKey = bin2hex(random_bytes(32));
    }

    public function isExpired(): bool
    {
        if (!$this->expiresAt) {
            return false;
        }
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getApiKey(): ?string { return $this->apiKey; }
    public function setApiKey(string $apiKey): self { $this->apiKey = $apiKey; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): self { $this->isActive = $isActive; return $this; }
    public function getRateLimit(): ?int { return $this->rateLimit; }
    public function setRateLimit(?int $rateLimit): self { $this->rateLimit = $rateLimit; return $this; }
    public function getExpiresAt(): ?\DateTimeImmutable { return $this->expiresAt; }
    public function setExpiresAt(?\DateTimeImmutable $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }
    public function getLastUsedAt(): ?\DateTimeImmutable { return $this->lastUsedAt; }
    public function setLastUsedAt(?\DateTimeImmutable $lastUsedAt): self { $this->lastUsedAt = $lastUsedAt; return $this; }
    public function getRequestCount(): int { return $this->requestCount; }
    public function setRequestCount(int $requestCount): self { $this->requestCount = $requestCount; return $this; }
    public function getLastRequestAt(): ?\DateTimeImmutable { return $this->lastRequestAt; }
    public function setLastRequestAt(?\DateTimeImmutable $lastRequestAt): self { $this->lastRequestAt = $lastRequestAt; return $this; }
    public function getIpWhitelist(): ?array { return $this->ipWhitelist; }
    public function setIpWhitelist(?array $ipWhitelist): self { $this->ipWhitelist = $ipWhitelist; return $this; }
    public function getPermissions(): ?array { return $this->permissions; }
    public function setPermissions(?array $permissions): self { $this->permissions = $permissions; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function recordRequest(): void
    {
        $now = new \DateTimeImmutable();
        if (!$this->lastRequestAt || $this->lastRequestAt < new \DateTimeImmutable('-1 hour')) {
            $this->requestCount = 1;
        } else {
            $this->requestCount++;
        }
        $this->lastRequestAt = $now;
        $this->lastUsedAt = $now;
    }

    public function __toString(): string
    {
        return $this->name ?? 'API Key';
    }
}
