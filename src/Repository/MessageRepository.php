<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(
 *     array $criteria,
 *     array $orderBy = null,
 *     $limit = null,
 *     $offset = null
 * )
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return int Returns the number of existing Message objects
     */
    public function getMessagesNumber()
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('count(m.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    public function findAllMessagesForPagination($limit, $offset)
    {
        return $this->findBy(
            [], // No criteria
            ['date' => 'desc'],
            $limit,
            $offset
        );

        /*$qb = $this->createQueryBuilder('m')
            ->leftJoin('m.user', 'u')
            ->addSelect('m.date')
            ->addSelect('m.content')
            ->addSelect('u.id as userId')
            ->addSelect('u.username')
            ->addSelect('u.firstName')
            ->addSelect('u.lastName')
            ->addSelect('u.fileExtension')
            ->orderBy('m.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb
            ->getQuery()
            ->getResult();
        */
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
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
    public function findOneBySomeField($value): ?Message
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
