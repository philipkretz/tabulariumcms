<?php

namespace App\Repository;

use App\Entity\Translation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translation::class);
    }

    public function findByLocale(string $locale, string $domain = 'messages'): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.locale = :locale')
            ->andWhere('t.domain = :domain')
            ->setParameter('locale', $locale)
            ->setParameter('domain', $domain)
            ->orderBy('t.transKey', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findTranslation(string $key, string $locale, string $domain = 'messages'): ?Translation
    {
        return $this->createQueryBuilder('t')
            ->where('t.transKey = :key')
            ->andWhere('t.locale = :locale')
            ->andWhere('t.domain = :domain')
            ->setParameter('key', $key)
            ->setParameter('locale', $locale)
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllLocales(): array
    {
        $result = $this->createQueryBuilder('t')
            ->select('DISTINCT t.locale')
            ->orderBy('t.locale', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'locale');
    }
}
