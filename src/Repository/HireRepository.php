<?php

namespace App\Repository;

use App\Entity\Bookings\Hire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hire[]    findAll()
 * @method Hire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hire::class);
    }

    // /**
    //  * @return Hire[] Returns an array of Hire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Hire
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
