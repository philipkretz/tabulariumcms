<?php

namespace App\Repository;

use App\Entity\Friend;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FriendRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friend::class);
    }

    public function findFriends(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('(f.user = :user OR f.friend = :user)')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Friend::STATUS_ACCEPTED)
            ->getQuery()
            ->getResult();
    }

    public function findPendingRequests(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.friend = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Friend::STATUS_PENDING)
            ->getQuery()
            ->getResult();
    }

    public function areFriends(User $user1, User $user2): bool
    {
        $result = $this->createQueryBuilder('f')
            ->where('((f.user = :user1 AND f.friend = :user2) OR (f.user = :user2 AND f.friend = :user1))')
            ->andWhere('f.status = :status')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->setParameter('status', Friend::STATUS_ACCEPTED)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
