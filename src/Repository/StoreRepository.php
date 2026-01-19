<?php

namespace App\Repository;

use App\Entity\Store;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Store::class);
    }

    /**
     * Find active stores
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.sortOrder', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find stores near a location
     * Returns stores with calculated distance
     */
    public function findNearLocation(float $latitude, float $longitude, int $maxResults = 10): array
    {
        $stores = $this->findActive();

        // Calculate distances and sort
        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $storesWithDistance = array_map(function(Store $store) use ($latitude, $longitude) {
            return [
                'store' => $store,
                'distance' => $store->getDistanceTo($latitude, $longitude)
            ];
        }, $stores);

        // Sort by distance
        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        usort($storesWithDistance, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // Return only the requested number of results
        return array_slice($storesWithDistance, 0, $maxResults);
    }
}
