<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class SocialConnection
{
    public const PROVIDER_GOOGLE = "google";
    public const PROVIDER_FACEBOOK = "facebook";
    public const PROVIDER_X = "x";

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private string $provider;

    #[ORM\Column(length: 255)]
    private string $providerId;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }
    public function getProvider(): string { return $this->provider; }
    public function setProvider(string $provider): static { $this->provider = $provider; return $this; }
    public function getProviderId(): string { return $this->providerId; }
    public function setProviderId(string $providerId): static { $this->providerId = $providerId; return $this; }
    public function getAccessToken(): ?string { return $this->accessToken; }
    public function setAccessToken(?string $accessToken): static { $this->accessToken = $accessToken; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }
}