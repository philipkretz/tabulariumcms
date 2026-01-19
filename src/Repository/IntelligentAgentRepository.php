<?php

namespace App\Repository;

use App\Entity\IntelligentAgent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IntelligentAgent>
 */
class IntelligentAgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntelligentAgent::class);
    }

    /**
     * Find all active agents
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find agents by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :type')
            ->andWhere('a.isActive = :active')
            ->setParameter('type', $type)
            ->setParameter('active', true)
            ->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find agents by trigger event
     */
    public function findByTriggerEvent(string $event): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.triggerEvent = :event')
            ->andWhere('a.isActive = :active')
            ->setParameter('event', $event)
            ->setParameter('active', true)
            ->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get agent statistics
     */
    public function getStatistics(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select([
                'COUNT(a.id) as total',
                'SUM(CASE WHEN a.isActive = 1 THEN 1 ELSE 0 END) as active',
                'SUM(a.executionCount) as totalExecutions',
                'SUM(a.successCount) as totalSuccesses',
                'SUM(a.failureCount) as totalFailures'
            ])
            ->getQuery()
            ->getSingleResult();

        // Calculate average success rate
        $totalExec = (int)($result['totalExecutions'] ?? 0);
        $totalSuccess = (int)($result['totalSuccesses'] ?? 0);
        $result['avgSuccessRate'] = $totalExec > 0
            ? round(($totalSuccess / $totalExec) * 100, 1)
            : 0;

        return $result;
    }
}
