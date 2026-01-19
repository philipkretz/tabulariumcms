<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Address>
 */
class AddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    public function save(Address $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Address $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.isDefault', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findDefaultByUser(User $user, string $type = 'personal'): ?Address
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->andWhere('a.type = :type')
            ->andWhere('a.isDefault = true')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function setDefault(User $user, Address $defaultAddress, string $type): void
    {
        // Clear existing default
        $this->createQueryBuilder('a')
            ->update()
            ->set('a.isDefault', 'false')
            ->where('a.user = :user')
            ->andWhere('a.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->execute();

        // Set new default
        $defaultAddress->setDefault(true);
        $this->save($defaultAddress, true);
    }
}