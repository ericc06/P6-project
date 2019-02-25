<?php
// src/Service/TrickManager.php

namespace App\Service;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//use Symfony\Component\Validator\Constraints\DateTime;

class TrickManager extends Controller
{
    private $em;

    public function __construct()
    {
        $this->em = $this->getDoctrine()->getManager();
    }

    // Inserts or updates a trick into the database.
    public function saveTrickToDB(Trick $trick)
    {
        if (null === $trick->getCreationDate()) {
            $trick->setCreationDate(new \DateTime('now'));
        }
        if (null === $trick->getLastUpdateDate()) {
            $trick->setLastUpdateDate(new \DateTime('now'));
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
