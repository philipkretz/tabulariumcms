<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $receiver = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isRead = false;

    #[ORM\Column(type: 'boolean')]
    private bool $senderDeleted = false;

    #[ORM\Column(type: 'boolean')]
    private bool $receiverDeleted = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function getId(): ?int { return $this->id; }
    public function getSender(): ?User { return $this->sender; }
    public function setSender(User $sender): static { $this->sender = $sender; return $this; }
    public function getReceiver(): ?User { return $this->receiver; }
    public function setReceiver(User $receiver): static { $this->receiver = $receiver; return $this; }
    public function getContent(): ?string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }
    public function isRead(): bool { return $this->isRead; }
    public function setIsRead(bool $isRead): static { $this->isRead = $isRead; return $this; }
    public function isSenderDeleted(): bool { return $this->senderDeleted; }
    public function setSenderDeleted(bool $senderDeleted): static { $this->senderDeleted = $senderDeleted; return $this; }
    public function isReceiverDeleted(): bool { return $this->receiverDeleted; }
    public function setReceiverDeleted(bool $receiverDeleted): static { $this->receiverDeleted = $receiverDeleted; return $this; }
    public function getSentAt(): ?\DateTimeImmutable { return $this->sentAt; }
    public function getReadAt(): ?\DateTimeImmutable { return $this->readAt; }
    public function setReadAt(?\DateTimeImmutable $readAt): static { $this->readAt = $readAt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->sentAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return 'Message from ' . $this->sender?->getUsername() . ' to ' . $this->receiver?->getUsername();
    }
}
