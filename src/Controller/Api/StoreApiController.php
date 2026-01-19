<?php

namespace App\Controller\Api;

use App\Repository\StoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/stores')]
class StoreApiController extends AbstractController
{
    public function __construct(
        private StoreRepository $storeRepository
    ) {
    }

    #[Route('/nearby', name: 'api_stores_nearby', methods: ['GET'])]
    public function findNearby(Request $request): JsonResponse
    {
        $latitude = $request->query->get('lat');
        $longitude = $request->query->get('lon');
        $maxResults = (int)$request->query->get('limit', 10);

        if (!$latitude || !$longitude) {
            return $this->json([
                'error' => 'Latitude and longitude are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $lat = (float)$latitude;
            $lon = (float)$longitude;

            $storesWithDistance = $this->storeRepository->findNearLocation($lat, $lon, $maxResults);

            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
            $result = array_map(function($item) {
                $store = $item['store'];
                return [
                    'id' => $store->getId(),
                    'name' => $store->getName(),
                    'address' => $store->getAddress(),
                    'city' => $store->getCity(),
                    'postalCode' => $store->getPostalCode(),
                    'country' => $store->getCountry(),
                    'fullAddress' => $store->getFullAddress(),
                    'phone' => $store->getPhone(),
                    'email' => $store->getEmail(),
                    'latitude' => $store->getLatitude(),
                    'longitude' => $store->getLongitude(),
                    'openingHours' => $store->getOpeningHours(),
                    'description' => $store->getDescription(),
                    'distance' => round($item['distance'], 2), // Distance in km
                    'distanceText' => round($item['distance'], 1) . ' km'
                ];
            }, $storesWithDistance);

            return $this->json([
                'success' => true,
                'userLocation' => [
                    'latitude' => $lat,
                    'longitude' => $lon
                ],
                'stores' => $result,
                'count' => count($result)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Error finding stores: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_store_details', methods: ['GET'])]
    public function getStoreDetails(int $id): JsonResponse
    {
        $store = $this->storeRepository->find($id);

        if (!$store) {
            return $this->json([
                'error' => 'Store not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'store' => [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'address' => $store->getAddress(),
                'city' => $store->getCity(),
                'postalCode' => $store->getPostalCode(),
                'country' => $store->getCountry(),
                'fullAddress' => $store->getFullAddress(),
                'phone' => $store->getPhone(),
                'email' => $store->getEmail(),
                'latitude' => $store->getLatitude(),
                'longitude' => $store->getLongitude(),
                'openingHours' => $store->getOpeningHours(),
                'description' => $store->getDescription(),
                'managerName' => $store->getManagerName(),
                'isActive' => $store->isActive()
            ]
        ]);
    }

    #[Route('', name: 'api_stores_list', methods: ['GET'])]
    public function listAll(): JsonResponse
    {
        $stores = $this->storeRepository->findActive();

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $result = array_map(function($store) {
            return [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'address' => $store->getAddress(),
                'city' => $store->getCity(),
                'postalCode' => $store->getPostalCode(),
                'country' => $store->getCountry(),
                'fullAddress' => $store->getFullAddress(),
                'latitude' => $store->getLatitude(),
                'longitude' => $store->getLongitude()
            ];
        }, $stores);

        return $this->json([
            'success' => true,
            'stores' => $result,
            'count' => count($result)
        ]);
    }
}
