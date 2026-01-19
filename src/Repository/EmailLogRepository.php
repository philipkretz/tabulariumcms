<?php

namespace App\Repository;

use App\Entity\EmailLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailLog>
 */
class EmailLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailLog::class);
    }

    /**
     * Find recent emails
     */
    public function findRecent(int $limit = 100): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find emails by status
     */
    public function findByStatus(string $status, int $limit = 100): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find emails to recipient
     */
    public function findByRecipient(string $recipient, int $limit = 50): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.recipient = :recipient')
            ->setParameter('recipient', $recipient)
            ->orderBy('e.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find failed emails that can be retried
     */
    public function findFailedForRetry(int $maxRetries = 3, int $limit = 50): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->andWhere('e.retryCount < :maxRetries')
            ->setParameter('status', EmailLog::STATUS_FAILED)
            ->setParameter('maxRetries', $maxRetries)
            ->orderBy('e.sentAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get email statistics
     */
    public function getStatistics(\DateTimeInterface $from = null, \DateTimeInterface $to = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select([
                'COUNT(e.id) as total',
                'SUM(CASE WHEN e.status = :sent THEN 1 ELSE 0 END) as sent',
                'SUM(CASE WHEN e.status = :failed THEN 1 ELSE 0 END) as failed',
                'SUM(CASE WHEN e.status = :pending THEN 1 ELSE 0 END) as pending',
                'SUM(CASE WHEN e.openedAt IS NOT NULL THEN 1 ELSE 0 END) as opened',
                'SUM(CASE WHEN e.clickedAt IS NOT NULL THEN 1 ELSE 0 END) as clicked'
            ])
            ->setParameter('sent', EmailLog::STATUS_SENT)
            ->setParameter('failed', EmailLog::STATUS_FAILED)
            ->setParameter('pending', EmailLog::STATUS_PENDING);

        if ($from) {
            $qb->andWhere('e.sentAt >= :from')
               ->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('e.sentAt <= :to')
               ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Get email statistics by template
     */
    public function getStatsByTemplate(): array
    {
        return $this->createQueryBuilder('e')
            ->select([
                'e.templateCode',
                'COUNT(e.id) as total',
                'SUM(CASE WHEN e.status = :sent THEN 1 ELSE 0 END) as sent',
                'SUM(CASE WHEN e.status = :failed THEN 1 ELSE 0 END) as failed'
            ])
            ->andWhere('e.templateCode IS NOT NULL')
            ->setParameter('sent', EmailLog::STATUS_SENT)
            ->setParameter('failed', EmailLog::STATUS_FAILED)
            ->groupBy('e.templateCode')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find emails by related entity
     */
    public function findByRelatedEntity(string $entityClass, int $entityId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.relatedEntity = :entity')
            ->andWhere('e.relatedEntityId = :entityId')
            ->setParameter('entity', $entityClass)
            ->setParameter('entityId', $entityId)
            ->orderBy('e.sentAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Delete old email logs
     */
    public function deleteOlderThan(\DateTimeInterface $date): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->andWhere('e.sentAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
