<?php
// src/Service/TrickManager.php

namespace App\Service;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;


//use Symfony\Component\Validator\Constraints\DateTime;

class TrickManager extends Controller
{
    private $em;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $this->getDoctrine()->getManager();
    }

    // Inserts or updates a trick into the database.
    public function saveTrickToDB(Trick $trick)
    {
        if (null === $trick->getCreationDate()) {
            $trick->setCreationDate(new \DateTime());
        }
        if (null === $trick->getLastUpdateDate()) {
            $trick->setLastUpdateDate(new \DateTime());
        }
        $this->em->persist($trick);
        $this->em->flush();
    }

    // Deletes a trick from the database.
    public function deleteTrickFromDB(Trick $trick)
    {
        $this->em->remove($trick);
        $this->em->flush();
    }
}
