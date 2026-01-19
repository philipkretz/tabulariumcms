<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ContactFormField
{
    public const TYPE_TEXT = "text";
    public const TYPE_EMAIL = "email";
    public const TYPE_PHONE = "phone";
    public const TYPE_TEXTAREA = "textarea";
    public const TYPE_SELECT = "select";
    public const TYPE_CHECKBOX = "checkbox";
    public const TYPE_RADIO = "radio";

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ContactForm::class, inversedBy: "fields")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ContactForm $form = null;

    #[ORM\Column(length: 255)]
    private string $label;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 50)]
    private string $type = self::TYPE_TEXT;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $placeholder = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $options = null;

    #[ORM\Column(type: "boolean")]
    private bool $isRequired = false;

    #[ORM\Column(type: "integer")]
    private int $sortOrder = 0;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $validation = null;

    public function getId(): ?int { return $this->id; }
    public function getForm(): ?ContactForm { return $this->form; }
    public function setForm(?ContactForm $form): static { $this->form = $form; return $this; }
    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getPlaceholder(): ?string { return $this->placeholder; }
    public function setPlaceholder(?string $placeholder): static { $this->placeholder = $placeholder; return $this; }
    public function getOptions(): ?array { return $this->options; }
    public function setOptions(?array $options): static { $this->options = $options; return $this; }
    public function isRequired(): bool { return $this->isRequired; }
    public function setIsRequired(bool $required): static { $this->isRequired = $required; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $order): static { $this->sortOrder = $order; return $this; }
    public function getValidation(): ?string { return $this->validation; }
    public function setValidation(?string $validation): static { $this->validation = $validation; return $this; }

    public function __toString(): string { return $this->label ?? "Form Field"; }
}