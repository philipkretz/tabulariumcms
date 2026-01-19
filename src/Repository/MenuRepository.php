<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    public function findByPosition(string $position): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.position = :position')
            ->andWhere('m.isActive = :active')
            ->andWhere('m.parent IS NULL')
            ->setParameter('position', $position)
            ->setParameter('active', true)
            ->orderBy('m.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
