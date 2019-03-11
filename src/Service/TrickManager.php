<?php
// src/Service/TrickManager.php

namespace App\Service;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\TrickGroup;
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
        //\dump($trick);

        $result = [];

        try {
            $this->em->persist($trick);
            $this->em->flush();

            $result['msg_type'] = 'success';
            $result['message'] = 'trick_saved_successfully';
            $result['dest_page'] = 'homepage';
        } catch (Exception $e) {
            $result['msg_type'] = 'danger';
            $result['message'] = $e->getMessage();
            $result['dest_page'] = 'trick_new';
        }

        return $result;
    }

    // Deletes a trick from the database.
    public function deleteTrickFromDB(Trick $trick)
    {
        $result = [];

        try {
            $this->em->remove($trick);
            $this->em->flush();

            $result['msg_type'] = 'success';
            $result['message'] = 'trick_deleted_successfully';
            $result['dest_page'] = 'homepage';
        } catch (Exception $e) {
            $result['msg_type'] = 'danger';
            $result['message'] = $e->getMessage();
            $result['dest_page'] = 'homepage';
        }

        return $result;
    }

    // Returns all tricks with their cover image for the homepage (no media).
    public function getAllTricksForIndexPage()
    {
        $tricks = $this->em->getRepository(Trick::class)
        ->findAllTricks();

        $tricksArray = [];

        foreach ($tricks as $key => $trick) {
            $tricksArray[$key]['id'] = $trick->getId();
            $tricksArray[$key]['name'] = $trick->getName();
            $tricksArray[$key]['slug'] = $trick->getSlug();
            $tricksArray[$key]['coverImage'] = $this->getCoverImageByTrickId($trick->getId());
        }
        
        //\dump($tricksArray);

        return $tricksArray;
    }

    // Returns the medias from a trick id.
    public function getMediasByTrickId($id)
    {
        return $this->em->getRepository(Media::class)
            ->findMediasByTrickIdOrderedByFileType($id);
    }

    // Returns the cover image file name from a trick id.
    public function getCoverImageByTrickId($id)
    {
        $cover_image_details = $this->em->getRepository(Media::class)->findDefaultCoverForTrickOrTheFirstOne($id);

        return $cover_image_details[0]->getId() . '.' . $cover_image_details[0]->getFileUrl();
    }
    // Returns the group name from a trick id.
    public function getGroupNameByTrickGroupId($id)
    {
        return $this->em->getRepository(TrickGroup::class)->findGroupNameByGroupId($id);
    }}
