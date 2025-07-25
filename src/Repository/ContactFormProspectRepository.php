<?php

namespace App\Repository;

use App\Entity\ContactFormProspect;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactFormProspect>
 */
class ContactFormProspectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactFormProspect::class);
    }

    public function save(ContactFormProspect $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByProspectIds($prospectIds): array
    {
        if ($prospectIds) {
            return $this->createQueryBuilder('p')
                ->andWhere('p.id IN (:prospectIds)')
                ->setParameter('prospectIds', $prospectIds)
                ->getQuery()
                ->getResult();
        }
        return [];
    }

    //    /**
    //     * @return ContactFormProspect[] Returns an array of ContactFormProspect objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ContactFormProspect
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
