<?php

namespace App\Repository;

use App\Entity\MimeType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MimeType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MimeType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MimeType[]    findAll()
 * @method MimeType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MimeTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MimeType::class);
    }

//    /**
//     * @return MimeType[] Returns an array of MimeType objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MimeType
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
