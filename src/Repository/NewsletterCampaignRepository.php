<?php

namespace App\Repository;

use App\Entity\NewsletterCampaign;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NewsletterCampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterCampaign::class);
    }

    public function save(NewsletterCampaign $campaign, bool $flush = false): void
    {
        $this->getEntityManager()->persist($campaign);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NewsletterCampaign $campaign, bool $flush = false): void
    {
        $this->getEntityManager()->remove($campaign);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findDrafts(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->setParameter('status', NewsletterCampaign::STATUS_DRAFT)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findScheduled(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.status = :status')
            ->andWhere('c.scheduledAt IS NOT NULL')
            ->andWhere('c.scheduledAt <= :now')
            ->setParameter('status', NewsletterCampaign::STATUS_DRAFT)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('c.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
