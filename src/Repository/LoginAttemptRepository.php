<?php

namespace App\Repository;

use App\Entity\LoginAttempt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LoginAttempt>
 */
class LoginAttemptRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAttempt::class);
    }

    /**
     * Count failed login attempts for a username within a time period
     */
    public function countRecentFailedAttemptsByUsername(string $username, \DateTimeInterface $since): int
    {
        return $this->createQueryBuilder('la')
            ->select('COUNT(la.id)')
            ->where('la.username = :username')
            ->andWhere('la.wasSuccessful = false')
            ->andWhere('la.attemptedAt >= :since')
            ->setParameter('username', $username)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count failed login attempts from an IP within a time period
     */
    public function countRecentFailedAttemptsByIp(string $ipAddress, \DateTimeInterface $since): int
    {
        return $this->createQueryBuilder('la')
            ->select('COUNT(la.id)')
            ->andWhere('la.ipAddress = :ip')
            ->andWhere('la.wasSuccessful = false')
            ->andWhere('la.attemptedAt >= :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get the last successful login for a username
     */
    public function getLastSuccessfulLogin(string $username): ?LoginAttempt
    {
        return $this->createQueryBuilder('la')
            ->where('la.username = :username')
            ->andWhere('la.wasSuccessful = true')
            ->orderBy('la.attemptedAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Clean up old login attempts (older than 30 days)
     */
    public function cleanupOldAttempts(): int
    {
        $cutoffDate = new \DateTime('-30 days');

        return $this->createQueryBuilder('la')
            ->delete()
            ->where('la.attemptedAt < :cutoff')
            ->setParameter('cutoff', $cutoffDate)
            ->getQuery()
            ->execute();
    }

    /**
     * Record a login attempt
     */
    public function recordAttempt(string $username, string $ipAddress, bool $wasSuccessful, ?string $userAgent = null): LoginAttempt
    {
        $attempt = new LoginAttempt();
        $attempt->setUsername($username);
        $attempt->setIpAddress($ipAddress);
        $attempt->setWasSuccessful($wasSuccessful);
        $attempt->setAttemptedAt(new \DateTime());
        $attempt->setUserAgent($userAgent);

        $this->getEntityManager()->persist($attempt);
        $this->getEntityManager()->flush();

        return $attempt;
    }
}
