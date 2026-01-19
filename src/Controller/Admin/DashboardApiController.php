<?php

namespace App\Controller\Admin;

use App\Service\DashboardStatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/api')]
#[IsGranted('ROLE_ADMIN')]
class DashboardApiController extends AbstractController
{
    public function __construct(
        private DashboardStatisticsService $statsService
    ) {
    }

    #[Route('/dashboard-stats', name: 'admin_dashboard_stats', methods: ['GET'])]
    public function getDashboardStats(): JsonResponse
    {
        try {
            $overview = $this->statsService->getOverviewStats();
            $sales = $this->statsService->getSalesLast3Months();
            $lastSold = $this->statsService->getLastSoldItems(10);
            $categories = $this->statsService->getMostPopularCategories(5);

            return $this->json([
                'overview' => $overview,
                'sales' => $sales,
                'lastSold' => $lastSold,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
