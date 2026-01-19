<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AiWorkflowStep
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AiWorkflow::class, inversedBy: "steps")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?AiWorkflow $workflow = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: "text")]
    private string $prompt;

    #[ORM\Column(type: "integer")]
    private int $sortOrder = 0;

    #[ORM\Column(length: 50)]
    private string $action = "generate_text";

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $parameters = null;

    public function getId(): ?int { return $this->id; }
    public function getWorkflow(): ?AiWorkflow { return $this->workflow; }
    public function setWorkflow(?AiWorkflow $workflow): static { $this->workflow = $workflow; return $this; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }
    public function getPrompt(): string { return $this->prompt; }
    public function setPrompt(string $prompt): static { $this->prompt = $prompt; return $this; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function setSortOrder(int $order): static { $this->sortOrder = $order; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }
    public function getParameters(): ?array { return $this->parameters; }
    public function setParameters(?array $parameters): static { $this->parameters = $parameters; return $this; }

    public function __toString(): string { return $this->name ?? "Workflow Step"; }
}