<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Media|null find($id, $lockMode = null, $lockVersion = null)
 * @method Media|null findOneBy(array $criteria, array $orderBy = null)
 * @method Media[]    findAll()
 * @method Media[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findMediasByTrickIdOrderedByFileType($trickId)
    {
        return $this->createQueryBuilder('m')
            // m.trick refers to the "trick" property on media
            // selects all the category data to avoid the query
            ->where('m.trick = :id')
            ->setParameter('id', $trickId)
            ->orderBy('m.fileType', 'ASC')
            ->getQuery()
            ->execute();
    }

    public function findDefaultCoverForTrickOrTheFirstOne($trickId)
    {
        $result = $this->createQueryBuilder('m') // . '.' . 'm.fileUrl')
            // m.trick refers to the "trick" property on media
            // selects all the category data to avoid the query
            ->where('m.trick = :id')
            ->andWhere('m.defaultCover = true')
            ->setParameter('id', $trickId)
            ->getQuery()
            ->getResult();

        if ((null === $result) || ([] === $result)) {
            $result = $this->createQueryBuilder('m') // . '.' . 'm.fileUrl')
            // m.trick refers to the "trick" property on media
            // selects all the category data to avoid the query
            ->where('m.trick = :id')
            ->andWhere('m.fileType = 0')
            ->setParameter('id', $trickId)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
        }
        return $result;
    }

    // /**
    //  * @return Media[] Returns an array of Media objects
    //  */
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
    public function findOneBySomeField($value): ?Media
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
