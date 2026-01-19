<?php

namespace App\Repository;

use App\Entity\PageVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PageVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageVisit::class);
    }

    public function getVisitsLastDays(int $days): array
    {
        $since = new \DateTimeImmutable(sprintf('-%d days', $days));
        return $this->createQueryBuilder('pv')
            ->select('DATE(pv.visitedAt) as day, COUNT(pv.id) as count')
            ->where('pv.visitedAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('day')
            ->orderBy('day', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTopUrls(int $days, int $limit = 10): array
    {
        $since = new \DateTimeImmutable(sprintf('-%d days', $days));
        return $this->createQueryBuilder('pv')
            ->select('pv.url, COUNT(pv.id) as count')
            ->where('pv.visitedAt >= :since')
            ->setParameter('since', $since)
            ->groupBy('pv.url')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function cleanupOlderThan(\DateTimeImmutable $date): void
    {
        $this->createQueryBuilder('pv')
            ->delete()
            ->where('pv.visitedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
