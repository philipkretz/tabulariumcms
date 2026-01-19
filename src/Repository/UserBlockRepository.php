<?php

namespace App\Repository;

use App\Entity\UserBlock;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBlock::class);
    }

    public function isBlocked(User $blocker, User $blocked): bool
    {
        $result = $this->findOneBy([
            'blocker' => $blocker,
            'blocked' => $blocked
        ]);

        return $result !== null;
    }

    public function findBlockedUsers(User $blocker): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.blocker = :blocker')
            ->setParameter('blocker', $blocker)
            ->getQuery()
            ->getResult();
    }
}
