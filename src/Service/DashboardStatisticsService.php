<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DashboardStatisticsService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function getSalesLast3Months(): array
    {
        $threeMonthsAgo = new \DateTimeImmutable('-3 months');

        $query = $this->em->createQuery(
            'SELECT
                DATE_FORMAT(o.createdAt, \'%Y-%m\') as month,
                SUM(o.total) as revenue,
                COUNT(o.id) as orders,
                SUM(o.totalItems) as items
            FROM App\Entity\Order o
            WHERE o.createdAt >= :date
            GROUP BY month
            ORDER BY month ASC'
        );

        $query->setParameter('date', $threeMonthsAgo);
        $results = $query->getResult();

        $monthly = [];
        $totalRevenue = 0;
        $totalOrders = 0;

        foreach ($results as $row) {
            $monthly[] = [
                'month' => $row['month'],
                'revenue' => (float) $row['revenue'],
                'orders' => (int) $row['orders'],
                'items' => (int) $row['items']
            ];
            $totalRevenue += (float) $row['revenue'];
            $totalOrders += (int) $row['orders'];
        }

        return [
            'monthly' => $monthly,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0
        ];
    }

    public function getLastSoldItems(int $limit = 10): array
    {
        $query = $this->em->createQuery(
            'SELECT
                oi.id,
                a.name as productName,
                oi.quantity,
                oi.price,
                o.customerName,
                o.createdAt
            FROM App\Entity\OrderItem oi
            JOIN oi.article a
            JOIN oi.order o
            ORDER BY o.createdAt DESC'
        );

        $query->setMaxResults($limit);
        $results = $query->getResult();

        $items = [];
        foreach ($results as $row) {
            $items[] = [
                'id' => $row['id'],
                'product' => $row['productName'],
                'quantity' => $row['quantity'],
                'price' => (float) $row['price'],
                'customer' => $row['customerName'],
                'date' => $row['createdAt']->format('Y-m-d H:i')
            ];
        }

        return $items;
    }

    public function getMostPopularCategories(int $limit = 10): array
    {
        $query = $this->em->createQuery(
            'SELECT
                c.id,
                c.name,
                COUNT(oi.id) as salesCount,
                SUM(oi.total) as revenue
            FROM App\Entity\Category c
            JOIN c.articles a
            JOIN App\Entity\OrderItem oi WITH oi.article = a
            GROUP BY c.id, c.name
            ORDER BY salesCount DESC'
        );

        $query->setMaxResults($limit);
        $results = $query->getResult();

        $totalRevenue = array_sum(array_column($results, 'revenue'));

        $categories = [];
        foreach ($results as $row) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'sales' => (int) $row['salesCount'],
                'revenue' => (float) $row['revenue'],
                'percentage' => $totalRevenue > 0 ? round(($row['revenue'] / $totalRevenue) * 100, 2) : 0
            ];
        }

        return $categories;
    }

    public function getOverviewStats(): array
    {
        $totalOrders = $this->em->createQuery('SELECT COUNT(o.id) FROM App\Entity\Order o')
            ->getSingleScalarResult();

        $totalArticles = $this->em->createQuery('SELECT COUNT(a.id) FROM App\Entity\Article a')
            ->getSingleScalarResult();

        $totalRevenue = $this->em->createQuery('SELECT SUM(o.total) FROM App\Entity\Order o')
            ->getSingleScalarResult();

        $pendingOrders = $this->em->createQuery(
            'SELECT COUNT(o.id) FROM App\Entity\Order o WHERE o.status = :status'
        )
            ->setParameter('status', 'pending')
            ->getSingleScalarResult();

        $lowStockArticles = $this->em->createQuery(
            'SELECT COUNT(a.id) FROM App\Entity\Article a WHERE a.stock < 10 AND a.stock > 0'
        )
            ->getSingleScalarResult();

        return [
            'total_orders' => (int) $totalOrders,
            'total_articles' => (int) $totalArticles,
            'total_revenue' => (float) ($totalRevenue ?? 0),
            'pending_orders' => (int) $pendingOrders,
            'low_stock_articles' => (int) $lowStockArticles,
        ];
    }
}
