<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

     /**
      * @return Item[] Returns an array of Item objects
      */
    public function findAllTagsOrderedByItem($id, QueryBuilder $qb = null)
    {
        return $this->getOrCreateQueryBuilder($qb)
            ->andWhere('i.collection = ' . $id)
            ->leftJoin('i.tags', 'tag')
            ->addSelect('tag')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Item[] Returns an array of Item objects
     */
    public function findAllCollectionsOrderedByItem(QueryBuilder $qb = null)
    {
        return $this->getOrCreateQueryBuilder($qb)
            ->leftJoin('i.collection', 'collection')
            ->addSelect('collection')
            ->getQuery()
            ->getResult()
            ;
    }

    private function getOrCreateQueryBuilder(QueryBuilder $qb = null): QueryBuilder
    {
        return $qb ?: $this->createQueryBuilder('i');
    }


    /*
    public function findOneBySomeField($value): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
