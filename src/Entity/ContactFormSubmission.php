<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ContactFormSubmission
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContactForm::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ContactForm $form = null;

    #[ORM\Column(type: "json")]
    private array $data = [];

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: "boolean")]
    private bool $isRead = false;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $submittedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getForm(): ?ContactForm { return $this->form; }
    public function setForm(ContactForm $form): static { $this->form = $form; return $this; }
    public function getData(): array { return $this->data; }
    public function setData(array $data): static { $this->data = $data; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ip): static { $this->ipAddress = $ip; return $this; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): static { $this->userAgent = $userAgent; return $this; }
    public function isRead(): bool { return $this->isRead; }
    public function setIsRead(bool $read): static { $this->isRead = $read; return $this; }
    public function getSubmittedAt(): ?\DateTimeImmutable { return $this->submittedAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->submittedAt = new \DateTimeImmutable(); }

    public function __toString(): string { 
        return "Submission #" . $this->id . " - " . $this->submittedAt?->format("Y-m-d H:i");
    }
}