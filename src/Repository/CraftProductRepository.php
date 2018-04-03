<?php

namespace App\Repository;

use App\Entity\CraftProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CraftProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method CraftProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method CraftProduct[]    findAll()
 * @method CraftProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CraftProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CraftProduct::class);
    }

//    /**
//     * @return CraftProduct[] Returns an array of CraftProduct objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CraftProduct
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
