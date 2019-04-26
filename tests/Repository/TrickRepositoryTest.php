<?php

// tests/Repository/TrickRepositoryTest.php
namespace App\Tests\Repository;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Psr\Log\LoggerInterface;

// Doctrine data fixtures must be loaded before executing
// these tests.
// WARNING: Loading fixture erases the database content!

class TrickRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    //private $logger;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testGetTricksNumber()
    {
        $tricksNumber = $this->entityManager
            ->getRepository(Trick::class)
            ->getTricksNumber()
        ;

        $this->assertEquals(11, $tricksNumber);
    }

    /**
     * @dataProvider trickPaginationParams
     */
    public function testFindAllTricksForPagination($limit, $offset, $tricksNbr)
    {
        $tricksArray = $this->entityManager
            ->getRepository(Trick::class)
            ->findAllTricksForPagination($limit, $offset)
        ;

        $this->assertEquals($tricksNbr, sizeof($tricksArray));
    }

    public function trickPaginationParams()
    {
        return [
            [5,  0, 5], // Getting the first 5 tricks out of 11.
            [5,  5, 5], // Getting the 6th to 10th tricks out of 11.
            [5, 10, 1]  // Getting the 11th (and last) trick.
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
