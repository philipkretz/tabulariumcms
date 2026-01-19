<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Find orders by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder("o")
            ->where("o.status = :status")
            ->setParameter("status", $status)
            ->orderBy("o.createdAt", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * Find orders by customer
     */
    public function findByCustomer($customer): array
    {
        return $this->createQueryBuilder("o")
            ->where("o.customer = :customer")
            ->setParameter("customer", $customer)
            ->orderBy("o.createdAt", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent orders
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder("o")
            ->orderBy("o.createdAt", "DESC")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get total revenue for a date range
     */
    public function getTotalRevenue(?\DateTimeImmutable $startDate = null, ?\DateTimeImmutable $endDate = null): float
    {
        $qb = $this->createQueryBuilder("o")
            ->select("SUM(o.total)")
            ->where("o.status IN (:completedStatuses)")
            ->setParameter("completedStatuses", [Order::STATUS_DELIVERED, Order::STATUS_PROCESSING]);

        if ($startDate) {
            $qb->andWhere("o.createdAt >= :startDate")
               ->setParameter("startDate", $startDate);
        }

        if ($endDate) {
            $qb->andWhere("o.createdAt <= :endDate")
               ->setParameter("endDate", $endDate);
        }

        return (float) $qb->getQuery()->getSingleScalarResult();
    }
}
