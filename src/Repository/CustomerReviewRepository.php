<?php

namespace App\Repository;

use App\Entity\CustomerReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CustomerReview>
 */
class CustomerReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CustomerReview::class);
    }

    /**
     * Find latest 5-star reviews
     */
    public function findLatestFiveStarReviews(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.rating = :rating')
            ->andWhere('r.isActive = :isActive')
            ->setParameter('rating', 5)
            ->setParameter('isActive', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find featured reviews
     */
    public function findFeaturedReviews(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.isFeatured = :isFeatured')
            ->andWhere('r.isActive = :isActive')
            ->setParameter('isFeatured', true)
            ->setParameter('isActive', true)
            ->orderBy('r.sortOrder', 'DESC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find reviews by rating
     */
    public function findByRating(int $rating, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.rating = :rating')
            ->andWhere('r.isActive = :isActive')
            ->setParameter('rating', $rating)
            ->setParameter('isActive', true)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Delete all reviews
     */
    public function deleteAll(): int
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute();
    }

    /**
     * Get average rating
     */
    public function getAverageRating(): float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.isActive = :isActive')
            ->setParameter('isActive', true)
            ->getQuery()
            ->getSingleScalarResult();

        return round((float)$result, 2);
    }

    /**
     * Count reviews by rating
     */
    public function countByRating(): array
    {
        $results = $this->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.id) as count')
            ->andWhere('r.isActive = :isActive')
            ->setParameter('isActive', true)
            ->groupBy('r.rating')
            ->orderBy('r.rating', 'DESC')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($results as $result) {
            $counts[$result['rating']] = (int)$result['count'];
        }

        return $counts;
    }
}
