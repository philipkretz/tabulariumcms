<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(columns: ['created_at'], name: 'idx_activity_created')]
#[ORM\Index(columns: ['action_type'], name: 'idx_activity_type')]
#[ORM\Index(columns: ['user_id'], name: 'idx_activity_user')]
class ActivityLog
{
    // Action types
    public const TYPE_USER_REGISTER = 'user_register';
    public const TYPE_USER_LOGIN = 'user_login';
    public const TYPE_USER_LOGOUT = 'user_logout';
    public const TYPE_ADMIN_LOGIN = 'admin_login';
    public const TYPE_ADMIN_LOGOUT = 'admin_logout';
    public const TYPE_SELLER_REGISTER = 'seller_register';
    public const TYPE_SELLER_APPROVED = 'seller_approved';
    public const TYPE_SELLER_REJECTED = 'seller_rejected';
    public const TYPE_PRODUCT_CREATED = 'product_created';
    public const TYPE_PRODUCT_UPDATED = 'product_updated';
    public const TYPE_PRODUCT_DELETED = 'product_deleted';
    public const TYPE_ORDER_CREATED = 'order_created';
    public const TYPE_ORDER_UPDATED = 'order_updated';
    public const TYPE_ORDER_PAID = 'order_paid';
    public const TYPE_ORDER_SHIPPED = 'order_shipped';
    public const TYPE_ORDER_COMPLETED = 'order_completed';
    public const TYPE_ORDER_CANCELLED = 'order_cancelled';
    public const TYPE_PAYMENT_SUCCESS = 'payment_success';
    public const TYPE_PAYMENT_FAILED = 'payment_failed';
    public const TYPE_SELLER_SALE = 'seller_sale';
    public const TYPE_SELLER_PAYOUT = 'seller_payout';
    public const TYPE_PAGE_CREATED = 'page_created';
    public const TYPE_PAGE_UPDATED = 'page_updated';
    public const TYPE_PAGE_DELETED = 'page_deleted';
    public const TYPE_POST_CREATED = 'post_created';
    public const TYPE_POST_UPDATED = 'post_updated';
    public const TYPE_POST_DELETED = 'post_deleted';
    public const TYPE_SETTINGS_UPDATED = 'settings_updated';
    public const TYPE_ADMIN_ACTION = 'admin_action';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 50)]
    private ?string $actionType = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $user = null;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $entityType = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $entityId = null;

    #[ORM\Column(type: "json", nullable: true)]
    private ?array $metadata = null;

    #[ORM\Column(type: "string", length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: "datetime_immutable")]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): static
    {
        $this->actionType = $actionType;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(?string $entityType): static
    {
        $this->entityType = $entityType;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return sprintf('[%s] %s', $this->actionType ?? '', $this->description ?? '');
    }
}
