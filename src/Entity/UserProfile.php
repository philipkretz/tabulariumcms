<?php

namespace App\Entity;

use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'profile')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $coverPhoto = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPublic = true;

    #[ORM\Column(type: 'boolean')]
    private bool $allowFriendRequests = true;

    #[ORM\Column(type: 'boolean')]
    private bool $allowMessages = true;

    #[ORM\Column(type: 'json')]
    private array $socialLinks = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $customTemplate = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    private ?string $profileSlug = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $profileViews = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastActive = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $profileApproved = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $tagline = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $interests = [];

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $lookingFor = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $offering = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getCoverPhoto(): ?string
    {
        return $this->coverPhoto;
    }

    public function setCoverPhoto(?string $coverPhoto): static
    {
        $this->coverPhoto = $coverPhoto;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
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

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;
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

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->isPublic = $isPublic;
        return $this;
    }

    public function getAllowFriendRequests(): bool
    {
        return $this->allowFriendRequests;
    }

    public function setAllowFriendRequests(bool $allowFriendRequests): static
    {
        $this->allowFriendRequests = $allowFriendRequests;
        return $this;
    }

    public function getAllowMessages(): bool
    {
        return $this->allowMessages;
    }

    public function setAllowMessages(bool $allowMessages): static
    {
        $this->allowMessages = $allowMessages;
        return $this;
    }

    public function getSocialLinks(): array
    {
        return $this->socialLinks;
    }

    public function setSocialLinks(array $socialLinks): static
    {
        $this->socialLinks = $socialLinks;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

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

    public function getCustomTemplate(): ?string
    {
        return $this->customTemplate;
    }

    public function setCustomTemplate(?string $customTemplate): static
    {
        $this->customTemplate = $customTemplate;
        return $this;
    }

    public function getProfileSlug(): ?string
    {
        return $this->profileSlug;
    }

    public function setProfileSlug(?string $profileSlug): static
    {
        $this->profileSlug = $profileSlug;
        return $this;
    }

    public function getProfileViews(): int
    {
        return $this->profileViews;
    }

    public function setProfileViews(int $profileViews): static
    {
        $this->profileViews = $profileViews;
        return $this;
    }

    public function incrementProfileViews(): static
    {
        $this->profileViews++;
        return $this;
    }

    public function getLastActive(): ?\DateTimeImmutable
    {
        return $this->lastActive;
    }

    public function setLastActive(?\DateTimeImmutable $lastActive): static
    {
        $this->lastActive = $lastActive;
        return $this;
    }

    public function updateLastActive(): static
    {
        $this->lastActive = new \DateTimeImmutable();
        return $this;
    }

    public function isProfileApproved(): bool
    {
        return $this->profileApproved;
    }

    public function setProfileApproved(bool $profileApproved): static
    {
        $this->profileApproved = $profileApproved;
        return $this;
    }

    public function getTagline(): ?string
    {
        return $this->tagline;
    }

    public function setTagline(?string $tagline): static
    {
        $this->tagline = $tagline;
        return $this;
    }

    public function getInterests(): ?array
    {
        return $this->interests ?? [];
    }

    public function setInterests(?array $interests): static
    {
        $this->interests = $interests;
        return $this;
    }

    public function getLookingFor(): ?string
    {
        return $this->lookingFor;
    }

    public function setLookingFor(?string $lookingFor): static
    {
        $this->lookingFor = $lookingFor;
        return $this;
    }

    public function getOffering(): ?string
    {
        return $this->offering;
    }

    public function setOffering(?string $offering): static
    {
        $this->offering = $offering;
        return $this;
    }

    public function getPublicUrl(): string
    {
        $slug = $this->profileSlug ?? $this->user?->getUsername() ?? $this->id;
        return '/profile/' . $slug;
    }

    public function __toString(): string
    {
        return $this->user?->getUsername() ?? 'UserProfile #' . $this->id;
    }
}
