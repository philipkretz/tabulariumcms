<?php

namespace App\Service;

use App\Entity\Article;
use App\Entity\Store;
use App\Entity\ProductStock;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class StockAvailabilityService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private SiteSettingsRepository $settingsRepo
    ) {}

    public function getSelectedStore(): ?Store
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!($request)) {
            return null;
        }

        $session = $request->getSession();
        $storeId = $session->get("selected_store_id");

        if ($storeId) {
            return $this->em->getRepository(Store::class)->find($storeId);
        }

        return null;
    }

    public function setSelectedStore(Store $store): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $session = $request->getSession();
            $session->set("selected_store_id", $store->getId());
        }
    }

    public function isStoreSelectionEnabled(): bool
    {
        $setting = $this->settingsRepo->findOneBy(["settingKey" => "enable_store_selection"]);
        return $setting && $setting->getValue() === "1";
    }

    public function isStoreSelectionRequired(): bool
    {
        $setting = $this->settingsRepo->findOneBy(["settingKey" => "require_store_selection"]);
        return $setting && $setting->getValue() === "1";
    }

    public function showStoreStock(): bool
    {
        $setting = $this->settingsRepo->findOneBy(["settingKey" => "show_store_stock"]);
        return $setting && $setting->getValue() === "1";
    }

    public function getStockForProduct(Article $article, ?Store $store = null): ?ProductStock
    {
        if (!($store)) {
            $store = $this->getSelectedStore();
        }

        if (!($store)) {
            return null;
        }

        return $this->em->getRepository(ProductStock::class)->findOneBy([
            "article" => $article,
            "store" => $store
        ]);
    }

    public function isAvailableInStore(Article $article, ?Store $store = null): bool
    {
        $stock = $this->getStockForProduct($article, $store);
        return $stock ? $stock->isInStock() : false;
    }

    public function getAvailableStores(Article $article): array
    {
        $stocks = $this->em->getRepository(ProductStock::class)->findBy([
            "article" => $article
        ]);

        $availableStores = [];
        foreach ($stocks as $stock) {
            if ($stock->isInStock() && $stock->getStore()->isActive()) {
                $availableStores[] = [
                    "store" => $stock->getStore(),
                    "quantity" => $stock->getAvailableQuantity(),
                    "price" => $stock->getStorePrice() ?? $article->getPrice()
                ];
            }
        }

        return $availableStores;
    }

    public function getNearbyStoresWithStock(Article $article, float $lat, float $lng, float $radius = 50): array
    {
        $allStores = $this->em->getRepository(Store::class)->findBy([
            "isActive" => true,
            "showOnMap" => true
        ]);

        $nearbyStores = [];
        foreach ($allStores as $store) {
            if (!($store->getLatitude()) || !($store->getLongitude())) {
                continue;
            }

            $distance = $this->calculateDistance(
                $lat,
                $lng,
                (float) $store->getLatitude(),
                (float) $store->getLongitude()
            );

            if ($distance <= $radius) {
                $stock = $this->getStockForProduct($article, $store);
                if ($stock && $stock->isInStock()) {
                    $nearbyStores[] = [
                        "store" => $store,
                        "distance" => round($distance, 2),
                        "quantity" => $stock->getAvailableQuantity(),
                        "price" => $stock->getStorePrice() ?? $article->getPrice()
                    ];
                }
            }
        }

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        usort($nearbyStores, fn($a, $b) => $a["distance"] <=> $b["distance"]);

        return $nearbyStores;
    }

    private function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
}
