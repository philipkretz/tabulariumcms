<?php

namespace App\Entity;

use App\Repository\IntelligentAgentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IntelligentAgentRepository::class)]
#[ORM\Table(name: 'intelligent_agent')]
#[ORM\HasLifecycleCallbacks]
class IntelligentAgent
{
    public const TYPE_CUSTOMER_SUPPORT = 'customer_support';
    public const TYPE_SALES = 'sales';
    public const TYPE_CONTENT_GENERATOR = 'content_generator';
    public const TYPE_DATA_ANALYST = 'data_analyst';
    public const TYPE_RECOMMENDATION = 'recommendation';
    public const TYPE_CHATBOT = 'chatbot';
    public const TYPE_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $type = self::TYPE_CUSTOM;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $systemPrompt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $configuration = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tools = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $workflow = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $model = 'gpt-4';

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $temperature = 0.7;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $maxTokens = 2000;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $priority = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $triggerEvent = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $triggerConditions = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $apiEndpoint = null;

    #[ORM\Column(type: 'integer')]
    private int $executionCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $successCount = 0;

    #[ORM\Column(type: 'integer')]
    private int $failureCount = 0;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastExecutedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function setSystemPrompt(string $systemPrompt): static
    {
        $this->systemPrompt = $systemPrompt;
        return $this;
    }

    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    public function setConfiguration(?array $configuration): static
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function getTools(): ?array
    {
        return $this->tools;
    }

    public function setTools(?array $tools): static
    {
        $this->tools = $tools;
        return $this;
    }

    public function getWorkflow(): ?array
    {
        return $this->workflow;
    }

    public function setWorkflow(?array $workflow): static
    {
        $this->workflow = $workflow;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }

    public function setMaxTokens(?int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    public function getTriggerEvent(): ?string
    {
        return $this->triggerEvent;
    }

    public function setTriggerEvent(?string $triggerEvent): static
    {
        $this->triggerEvent = $triggerEvent;
        return $this;
    }

    public function getTriggerConditions(): ?array
    {
        return $this->triggerConditions;
    }

    public function setTriggerConditions(?array $triggerConditions): static
    {
        $this->triggerConditions = $triggerConditions;
        return $this;
    }

    public function getApiEndpoint(): ?string
    {
        return $this->apiEndpoint;
    }

    public function setApiEndpoint(?string $apiEndpoint): static
    {
        $this->apiEndpoint = $apiEndpoint;
        return $this;
    }

    public function getExecutionCount(): int
    {
        return $this->executionCount;
    }

    public function incrementExecutionCount(): static
    {
        $this->executionCount++;
        return $this;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function incrementSuccessCount(): static
    {
        $this->successCount++;
        return $this;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function incrementFailureCount(): static
    {
        $this->failureCount++;
        return $this;
    }

    public function getSuccessRate(): float
    {
        if ($this->executionCount === 0) {
            return 0.0;
        }
        return round(($this->successCount / $this->executionCount) * 100, 2);
    }

    public function getLastExecutedAt(): ?\DateTimeImmutable
    {
        return $this->lastExecutedAt;
    }

    public function setLastExecutedAt(?\DateTimeImmutable $lastExecutedAt): static
    {
        $this->lastExecutedAt = $lastExecutedAt;
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
        return $this->name ?? 'Intelligent Agent';
    }
}
