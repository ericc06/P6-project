<?php

namespace App\Repository;

use App\Entity\Trick;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Trick|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trick|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trick[]    findAll()
 * @method Trick[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrickRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Trick::class);
    }

    /**
     * @return int Returns the number of existing Trick objects
     */
    public function getTricksNumber()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('count(t.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Trick[] Returns an array of Trick objects
     */
    public function findAllTricksForPagination($limit, $offset)
    {
        return $this->findBy(
            [], // No criteria
            ['name' => 'asc'],
            $limit,
            $offset
        );
    }

          // /**
    //  * @return Trick[] Returns an array of Trick objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Trick
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // Returns all tricks with their cover image.
    /*public function findAllTricksForIndexPage()
    {
        $tricks = $this->findAll();

        foreach ($tricks as $trick) {
            $image = $this->createQueryBuilder('t')
                ->getQuery()
                ->getResult();
        }

        return $tricks;
    }

    public function findAllMediasByTrickId($trickId)
    {
        return $this->createQueryBuilder('t')
            ->from('APP\Entity\Media', 'm')
            ->andWhere('trick_id = :id')
            ->setParameter('id', $trickId)
            ->getQuery()
            ->getResult();
    }
    */
}
