<?php

namespace App\Repository;

use App\Entity\DatiTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatiTransaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatiTransaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatiTransaction[]    findAll()
 * @method DatiTransaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatiTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatiTransaction::class);
    }

    // /**
    //  * @return DatiTransaction[] Returns an array of DatiTransaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DatiTransaction
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
