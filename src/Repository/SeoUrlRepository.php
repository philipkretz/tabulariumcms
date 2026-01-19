<?php

namespace App\Repository;

use App\Entity\SeoUrl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeoUrl>
 */
class SeoUrlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoUrl::class);
    }

    public function save(SeoUrl $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SeoUrl $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUrlAndLocale(string $url, string $locale): ?SeoUrl
    {
        return $this->createQueryBuilder('s')
            ->where('s.url = :url')
            ->andWhere('s.locale = :locale')
            ->andWhere('s.isActive = true')
            ->setParameter('url', $url)
            ->setParameter('locale', $locale)
            ->orderBy('s.priority', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByLocale(string $locale): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('s.priority', 'DESC')
            ->addOrderBy('s.url', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = true')
            ->orderBy('s.priority', 'DESC')
            ->addOrderBy('s.url', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findDuplicateUrls(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.url', 'COUNT(s.id) as count', 'GROUP_CONCAT(s.id) as ids')
            ->groupBy('s.url', 's.locale')
            ->having('COUNT(s.id) > 1')
            ->getQuery()
            ->getResult();
    }

    public function findByRoute(string $route, array $parameters = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.route = :route')
            ->setParameter('route', $route);

        foreach ($parameters as $key => $value) {
            $qb->andWhere("JSON_CONTAINS(s.parameters, :param_$key) = true")
               ->setParameter("param_$key", json_encode([$key => $value]));
        }

        return $qb->orderBy('s.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }
}