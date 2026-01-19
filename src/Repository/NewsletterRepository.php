<?php

namespace App\Repository;

use App\Entity\Newsletter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Newsletter::class);
    }

    public function findByToken(string $token): ?Newsletter
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function findActiveSubscribers(): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.isActive = :active')
            ->andWhere('n.isConfirmed = :confirmed')
            ->setParameter('active', true)
            ->setParameter('confirmed', true)
            ->getQuery()
            ->getResult();
    }
}
