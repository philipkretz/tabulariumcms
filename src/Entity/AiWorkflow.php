<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class AiWorkflow
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private string $aiProvider = "openai";

    #[ORM\Column(type: "boolean")]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: AiWorkflowStep::class, mappedBy: "workflow", cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["sortOrder" => "ASC"])]
    private Collection $steps;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct() { $this->steps = new ArrayCollection(); }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getAiProvider(): string { return $this->aiProvider; }
    public function setAiProvider(string $provider): static { $this->aiProvider = $provider; return $this; }
    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $active): static { $this->isActive = $active; return $this; }
    public function getSteps(): Collection { return $this->steps; }
    public function addStep(AiWorkflowStep $step): static {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setWorkflow($this);
        }
        return $this;
    }
    public function removeStep(AiWorkflowStep $step): static {
        if ($this->steps->removeElement($step) && $step->getWorkflow() === $this) {
            $step->setWorkflow(null);
        }
        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function __toString(): string { return $this->name ?? "AI Workflow"; }
}