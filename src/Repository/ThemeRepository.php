<?php

namespace App\Repository;

use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    public function save(Theme $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Theme $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActive(): ?Theme
    {
        return $this->createQueryBuilder('t')
            ->where('t.isActive = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDefault(): ?Theme
    {
        return $this->createQueryBuilder('t')
            ->where('t.isDefault = true')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.category = :category')
            ->setParameter('category', $category)
            ->orderBy('t.displayName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.isActive = true')
            ->orderBy('t.displayName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function setActive(Theme $theme): void
    {
        // Clear all active flags
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.isActive', 'false')
            ->where('t.isActive = true')
            ->getQuery()
            ->execute();

        // Set new active theme
        $theme->setActive(true);
        $this->save($theme, true);
    }

    public function setDefault(Theme $theme): void
    {
        // Clear all default flags
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.isDefault', 'false')
            ->where('t.isDefault = true')
            ->getQuery()
            ->execute();

        // Set new default theme
        $theme->setDefault(true);
        $this->save($theme, true);
    }

    public function findUserThemes(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.category = :category')
            ->setParameter('category', 'user')
            ->orderBy('t.displayName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}