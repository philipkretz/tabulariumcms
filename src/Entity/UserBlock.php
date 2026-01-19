<?php

namespace App\Entity;

use App\Repository\UserBlockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBlockRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'user_block')]
#[ORM\UniqueConstraint(name: 'unique_block', columns: ['blocker_id', 'blocked_id'])]
class UserBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'blocker_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $blocker = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'blocked_id', nullable: false, onDelete: 'CASCADE')]
    private ?User $blocked = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $blockedAt = null;

    public function getId(): ?int { return $this->id; }
    public function getBlocker(): ?User { return $this->blocker; }
    public function setBlocker(User $blocker): static { $this->blocker = $blocker; return $this; }
    public function getBlocked(): ?User { return $this->blocked; }
    public function setBlocked(User $blocked): static { $this->blocked = $blocked; return $this; }
    public function getBlockedAt(): ?\DateTimeImmutable { return $this->blockedAt; }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->blockedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->blocker?->getUsername() . ' blocked ' . $this->blocked?->getUsername();
    }
}
