<?php

namespace App\Repository;

use App\Entity\OrderItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Get most sold articles
     */
    public function getMostSoldArticles(int $limit = 10): array
    {
        return $this->createQueryBuilder("oi")
            ->select("oi.articleName, oi.articleSku, SUM(oi.quantity) as totalSold, SUM(oi.subtotal) as totalRevenue")
            ->groupBy("oi.article")
            ->orderBy("totalSold", "DESC")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
