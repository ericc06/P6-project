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

    /*public function findTrickIdByName($name)
    {
    return $this->createQueryBuilder('t')
    // m.trick refers to the "trick" property on media
    // selects all the category data to avoid the query
    ->select('id')
    ->where('name = :name')
    ->setParameter('name', $name)
    ->getQuery()
    ->getSingleScalarResult();
    }
     */

    // Returns all tricks with their direct attributes (no media).
    public function findAllTricks()
    {
        $result = $this->createQueryBuilder('t')
        //->select('id', 'name', 'slug')
            ->getQuery()
            ->getResult();

        //\dump($result);

        return $result;
    }

    // Returns all tricks with their cover image.
    public function findAllTricksForIndexPage()
    {
        $tricks = $this->findAllTricks();

        foreach ($tricks as $trick) {
            $image = $this->createQueryBuilder('t')
            //->select('id', 'name', 'slug')
                ->getQuery()
                ->getResult();
        }



        return $tricks;
    }

    public function findAllMediasByTrickId($trickId)
    {
        return $this->createQueryBuilder('t')
            ->from('APP\Entity\Media', 'm')
        // m.trick refers to the "trick" property on media
        // selects all the category data to avoid the query
            ->andWhere('trick_id = :id')
            ->setParameter('id', $trickId)
            ->getQuery()
            ->getResult();
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
}
