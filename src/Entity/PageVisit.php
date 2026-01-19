<?php

namespace App\Entity;

use App\Repository\PageVisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageVisitRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_visited_at', columns: ['visited_at'])]
#[ORM\Index(name: 'idx_url', columns: ['url'])]
class PageVisit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 500)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $referer = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $visitedAt = null;

    public function __construct(?string $url = null)
    {
        $this->url = $url;
        $this->visitedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUrl(): ?string { return $this->url; }
    public function setUrl(string $url): static { $this->url = $url; return $this; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }
    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): static { $this->userAgent = $userAgent; return $this; }
    public function getReferer(): ?string { return $this->referer; }
    public function setReferer(?string $referer): static { $this->referer = $referer; return $this; }
    public function getVisitedAt(): ?\DateTimeImmutable { return $this->visitedAt; }
    public function setVisitedAt(\DateTimeImmutable $visitedAt): static { $this->visitedAt = $visitedAt; return $this; }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->visitedAt = new \DateTimeImmutable(); }
}
