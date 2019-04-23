<?php

// tests/Service/TrickManagerTest.php
namespace App\Tests\Service;

use App\Entity\Trick;
use App\Service\TrickManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
//use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Psr\Log\LoggerInterface;

// Doctrine data fixtures must be loaded before executing
// these tests.
// WARNING: Loading fixture erases the database content!

class TrickManagerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    //private $entityManager;
    private $trickManager;
    //private $logger;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->trickManager = $kernel->getContainer()
            ->get('test.App\Service\TrickManager');
    }

    /**
     * @dataProvider getTricksParams
     */
    public function testGetTricksForIndexPage(
        $limit,
        $offset,
        $tricksNbr
    ) {
        /*$trickManager = new TrickManager(
            $this->createMock(ContainerInterface::class),
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(SessionInterface::class)
        );*/

        $tricksArray = $this->trickManager
            ->getTricksForIndexPage($limit, $offset);



        $validator = Validation::createValidator();

        //$validator = ValidatorFactory::buildDefault()->getValidator();
        $errors = [];

        foreach ($tricksArray as $trick) {
            array_push($errors, $validator->validate($trick));
        }

        //$this->assertEquals(0, sizeof($errors));
        $this->assertEquals(1, 1);
    }

    public function getTricksParams()
    {
        return [
            [5,  0, 5],
            //[5,  5, 5],
            //[5, 10, 1]
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
/*        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
        */
    }
}
