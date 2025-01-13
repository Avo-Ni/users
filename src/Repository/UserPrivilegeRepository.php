<?php

namespace App\Repository;

use App\Entity\UserPrivilege;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPrivilege>
 */
class UserPrivilegeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPrivilege::class);
    }

    //    /**
    //     * @return UserPrivilege[] Returns an array of UserPrivilege objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserPrivilege
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function checkUserResourceAccess(int $userId, int $resourceId): bool
    {
        $result = $this->createQueryBuilder('up')
            ->select('1')
            ->where('up.user = :userId')
            ->andWhere('up.resource = :resourceId')
            ->andWhere('up.isAllowed = true')
            ->setParameter('userId', $userId)
            ->setParameter('resourceId', $resourceId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        return $result !== null;
    }

}
