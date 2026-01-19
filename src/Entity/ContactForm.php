<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class ContactForm
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $identifier;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $submitButtonText = "Submit";

    #[ORM\Column(type: "string", length: 255)]
    private string $successMessage = "Thank you! Your message has been sent.";

    #[ORM\Column(type: "boolean")]
    private bool $sendEmail = true;

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: ContactFormField::class, mappedBy: "form", cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["sortOrder" => "ASC"])]
    private Collection $fields;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getIdentifier(): string { return $this->identifier; }
    public function setIdentifier(string $identifier): static { $this->identifier = $identifier; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getSubmitButtonText(): string { return $this->submitButtonText; }
    public function setSubmitButtonText(string $text): static { $this->submitButtonText = $text; return $this; }
    public function getSuccessMessage(): string { return $this->successMessage; }
    public function setSuccessMessage(string $message): static { $this->successMessage = $message; return $this; }
    public function isSendEmail(): bool { return $this->sendEmail; }
    public function setSendEmail(bool $sendEmail): static { $this->sendEmail = $sendEmail; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): static { $this->isActive = $active; return $this; }
    public function getFields(): Collection { return $this->fields; }
    
    public function addField(ContactFormField $field): static
    {
        // phpcs:ignore Security.BadFunctions.Asserts -- Doctrine Collection methods, not SQL
        if (!$this->fields->contains($field)) {
            // phpcs:ignore Security.BadFunctions.Asserts -- Doctrine Collection methods, not SQL
            $this->fields->add($field);
            $field->setForm($this);
        }
        return $this;
    }

    public function removeField(ContactFormField $field): static
    {
        // phpcs:ignore Security.BadFunctions.Asserts -- Doctrine Collection methods, not SQL
        if ($this->fields->removeElement($field) && $field->getForm() === $this) {
            $field->setForm(null);
        }
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function __toString(): string { return $this->name ?? "Contact Form"; }
}