<?php

namespace App\Repository;

use App\Entity\PhotoSlide;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;


/**
 * @extends ServiceEntityRepository<PhotoSlide>
 */
class PhotoSlideRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhotoSlide::class);
    }

        public function save(PhotoSlide $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

        public function regenerateRanks(EntityManagerInterface $entityManager)
            {
                $slides = $this->findBy([], ['rank' => 'ASC']);
                $cpt = 1;
                foreach ($slides as $slide) {
                    $slide->setRank($cpt);
                    $entityManager->persist($slide);
                    $cpt++;
                }
            } 

    //    /**
    //     * @return PhotoSlide[] Returns an array of PhotoSlide objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PhotoSlide
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
