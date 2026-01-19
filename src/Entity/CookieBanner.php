<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class CookieBanner
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title = "We use cookies";

    #[ORM\Column(type: "text")]
    private string $message = "This website uses cookies to ensure you get the best experience on our website.";

    #[ORM\Column(length: 255)]
    private string $acceptButtonText = "Accept";

    #[ORM\Column(length: 255)]
    private string $declineButtonText = "Decline";

    #[ORM\Column(length: 255)]
    private string $settingsButtonText = "Settings";

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $privacyPolicyUrl = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imprintUrl = null;


    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\Column(type: "json")]
    private array $cookieCategories = [];

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getMessage(): string { return $this->message; }
    public function setMessage(string $message): static { $this->message = $message; return $this; }
    public function getAcceptButtonText(): string { return $this->acceptButtonText; }
    public function setAcceptButtonText(string $text): static { $this->acceptButtonText = $text; return $this; }
    public function getDeclineButtonText(): string { return $this->declineButtonText; }
    public function setDeclineButtonText(string $text): static { $this->declineButtonText = $text; return $this; }
    public function getSettingsButtonText(): string { return $this->settingsButtonText; }
    public function setSettingsButtonText(string $text): static { $this->settingsButtonText = $text; return $this; }
    public function getPrivacyPolicyUrl(): ?string { return $this->privacyPolicyUrl; }
    public function setPrivacyPolicyUrl(?string $url): static { $this->privacyPolicyUrl = $url; return $this; }
    public function getImprintUrl(): ?string { return $this->imprintUrl; }
    public function setImprintUrl(?string $url): static { $this->imprintUrl = $url; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): static { $this->isActive = $active; return $this; }
    public function getCookieCategories(): array { return $this->cookieCategories; }
    public function setCookieCategories(array $categories): static { $this->cookieCategories = $categories; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { 
        $this->createdAt = new \DateTimeImmutable();
        if (empty($this->cookieCategories)) {
            $this->cookieCategories = [
                ["name" => "necessary", "label" => "Necessary", "description" => "Required for the website to function", "required" => true],
                ["name" => "analytics", "label" => "Analytics", "description" => "Help us understand how you use our website", "required" => false],
                ["name" => "marketing", "label" => "Marketing", "description" => "Used for targeted advertising", "required" => false],
            ];
        }
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }
}