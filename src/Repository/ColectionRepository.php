<?php

namespace App\Repository;

use App\Entity\Colection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Colection>
 *
 * @method Colection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Colection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Colection[]    findAll()
 * @method Colection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ColectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Colection::class);
    }

//    /**
//     * @return Colection[] Returns an array of Colection objects
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

//    public function findOneBySomeField($value): ?Colection
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
