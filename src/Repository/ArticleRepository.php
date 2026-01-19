<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Find active articles
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find articles by category
     */
    public function findByCategory(int $categoryId, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('a.createdAt', 'DESC');

        if ($activeOnly) {
            $qb->andWhere('a.isActive = :active')
               ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find articles by type
     */
    public function findByType(string $type, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.type = :type')
            ->setParameter('type', $type)
            ->orderBy('a.createdAt', 'DESC');

        if ($activeOnly) {
            $qb->andWhere('a.isActive = :active')
               ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Search articles by name or SKU
     */
    public function search(string $query, bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.name LIKE :query OR a.sku LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('a.createdAt', 'DESC');

        if ($activeOnly) {
            $qb->andWhere('a.isActive = :active')
               ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find low stock articles
     */
    public function findLowStock(int $threshold = 10): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.stock < :threshold')
            ->andWhere('a.stock > 0')
            ->andWhere('a.isActive = :active')
            ->setParameter('threshold', $threshold)
            ->setParameter('active', true)
            ->orderBy('a.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find featured articles
     */
    public function findFeatured(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isFeatured = :featured')
            ->andWhere('a.isActive = :active')
            ->setParameter('featured', true)
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find bookable articles (rooms, tickets, timeslots)
     */
    public function findBookable(bool $activeOnly = true): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.type IN (:types)')
            ->setParameter('types', [
                Article::TYPE_ROOM ?? 'room',
                Article::TYPE_TICKET ?? 'ticket',
                Article::TYPE_TIMESLOT ?? 'timeslot'
            ])
            ->orderBy('a.name', 'ASC');

        if ($activeOnly) {
            $qb->andWhere('a.isActive = :active')
               ->setParameter('active', true);
        }

        return $qb->getQuery()->getResult();
    }
}
