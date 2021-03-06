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
        $qbuilder = $this->createQueryBuilder('m');
        $qbuilder->select('count(m.id)');

        return $qbuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return int Returns the number of existing Message objects for a trick
     */
    public function getMessagesNumberForTrick($trickId)
    {
        $qbuilder = $this->createQueryBuilder('m');
        $qbuilder->select('count(m.id)')
            ->where('m.trick = :id')
            ->setParameter('id', $trickId);

        return $qbuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    public function findTrickMsgForPagination($trickId, $limit, $offset)
    {
        return $this->findBy(
            ['trick' => $trickId],
            ['date' => 'desc'],
            $limit,
            $offset
        );
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
