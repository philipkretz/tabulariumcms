<?php

namespace App\Repository;

use App\Entity\UserProfile;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserProfileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProfile::class);
    }

    public function findByUser(User $user): ?UserProfile
    {
        return $this->findOneBy(['user' => $user]);
    }

    public function findPublicProfiles(int $limit = 20): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isPublic = :public')
            ->setParameter('public', true)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
