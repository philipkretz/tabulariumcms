<?php

namespace App\Controller\Admin;

use App\Entity\ActivityLog;
use App\Entity\Article;
use App\Entity\Order;
use App\Entity\Seller;
use App\Entity\SiteSettings;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Pool $adminPool
    ) {}

    public function dashboard(): Response
    {
        // Get default currency from site settings
        $currency = "€";
        try {
            $siteSettings = $this->em->getRepository(SiteSettings::class)->findOneBy([]);
            if ($siteSettings && $siteSettings->getDefaultCurrency()) {
                $currency = match($siteSettings->getDefaultCurrency()) {
                    "USD" => "$",
                    "GBP" => "£",
                    "CHF" => "CHF",
                    "JPY" => "¥",
                    "CAD" => "C$",
                    "AUD" => "A$",
                    default => "€"
                };
            }
        } catch (\Exception $e) {
            // Use default currency
        }

        // Get real statistics
        $stats = [
            "currency" => $currency,
            "totalOrders" => $this->getTotalOrders(),
            "totalRevenue" => $this->getTotalRevenue(),
            "totalProducts" => $this->getTotalProducts(),
            "totalUsers" => $this->getTotalUsers(),
            "totalSellers" => $this->getTotalSellers(),
            "recentOrders" => $this->getRecentOrders(),
            "topProducts" => $this->getTopProducts(),
            "revenueByMonth" => $this->getRevenueByMonth(),
            "ordersByStatus" => $this->getOrdersByStatus(),
            "recentActivity" => $this->getRecentActivity(),
        ];

        return $this->render("admin/dashboard.html.twig", [
            "stats" => $stats,
            "base_template" => "@SonataAdmin/standard_layout.html.twig",
            "admin_pool" => $this->adminPool
        ]);
    }

    private function getTotalOrders(): int
    {
        try {
            return $this->em->getRepository(Order::class)->count([]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalRevenue(): float
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $qb->select("SUM(o.total)")
               ->from(Order::class, "o")
               ->where("o.status IN (:statuses)")
               ->setParameter("statuses", [
                   Order::STATUS_PAYMENT_RECEIVED,
                   Order::STATUS_PROCESSING,
                   Order::STATUS_SHIPPED,
                   Order::STATUS_DELIVERED
               ]);

            return (float) ($qb->getQuery()->getSingleScalarResult() ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    private function getTotalProducts(): int
    {
        try {
            return $this->em->getRepository(Article::class)->count(["isActive" => true]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalUsers(): int
    {
        try {
            return $this->em->getRepository(User::class)->count([]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getTotalSellers(): int
    {
        try {
            return $this->em->getRepository(Seller::class)->count(["isActive" => true]);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getRecentOrders(): array
    {
        try {
            return $this->em->getRepository(Order::class)
                ->findBy([], ["createdAt" => "DESC"], 5);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTopProducts(): array
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $qb->select("a.name", "COUNT(oi.id) as orderCount")
               ->from("App\\Entity\\OrderItem", "oi")
               ->join("oi.article", "a")
               ->groupBy("a.id")
               ->orderBy("orderCount", "DESC")
               ->setMaxResults(5);
            
            return $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRevenueByMonth(): array
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $qb->select("DATE_FORMAT(o.createdAt, '%Y-%m') as month", "SUM(o.total) as revenue")
               ->from(Order::class, "o")
               ->where("o.createdAt >= :startDate")
               ->andWhere("o.status IN (:statuses)")
               ->setParameter("startDate", new \DateTime("-12 months"))
               ->setParameter("statuses", [
                   Order::STATUS_PAYMENT_RECEIVED,
                   Order::STATUS_PROCESSING,
                   Order::STATUS_SHIPPED,
                   Order::STATUS_DELIVERED
               ])
               ->groupBy("month")
               ->orderBy("month", "ASC");

            return $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getOrdersByStatus(): array
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $qb->select("o.status", "COUNT(o.id) as count")
               ->from(Order::class, "o")
               ->groupBy("o.status");
            
            return $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getRecentActivity(): array
    {
        try {
            return $this->em->getRepository(ActivityLog::class)
                ->findBy([], ["createdAt" => "DESC"], 10);
        } catch (\Exception $e) {
            return [];
        }
    }
}
