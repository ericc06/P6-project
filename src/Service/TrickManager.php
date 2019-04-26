<?php
// src/Service/TrickManager.php

namespace App\Service;

use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\Message;
use App\Entity\TrickGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TrickManager extends Controller
{
    protected $container;
    private $router;
    private $session;
    private $entityManager;

    public function __construct(
        Container $container,
        UrlGeneratorInterface $router,
        SessionInterface $session
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->session = $session;
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    // Inserts or updates a trick into the database.
    public function saveTrickToDB(Trick $trick)
    {
        $result = [];

        try {
            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            $result['is_successful'] = true;
            $result['msg_type'] = 'success';
            $result['message'] = 'trick_saved_successfully';
            $result['message_params'] = [];
            $result['dest_page'] = 'homepage';
        } catch (UniqueConstraintViolationException $e) {
            $result['is_successful'] = false;
            $result['msg_type'] = 'danger';
            $result['message'] = 'trick_name_already_exists';
            $result['message_params'] = [
                '%link_start%' => '<a href="'
                    . $this->generateUrl('trick_edit', [
                        'id' => $this->entityManager->getRepository(Trick::class)
                            ->findByName($trick->getName())[0]->getId()
                    ]) . '">',
                '%link_end%' => '</a>'
            ];
            $result['dest_page'] = 'trick_new';
            $result['trick'] = $trick;
        }

        return $result;
    }

    // Deletes a trick from the database.
    public function deleteTrickFromDB(Trick $trick)
    {
        $result = [];

        try {
            $this->entityManager->remove($trick);
            $this->entityManager->flush();

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

    // Stores a trick in the session.
    public function storeTrickInSession(Trick $trick)
    {
        // Uploaded files can't be stored into the session
        // because they can't be serialized.
        $this->session->set(
            'trick',
            serialize($this->dropUploadedFileForAllMedias($trick))
        );
        // It seems that the trick group must be stored separately
        // and must be "merged" after being read from the session
        // to avoid "Entity passed to the choice field must be managed.
        // Maybe you forget to persist it in the entity manager?" error.
        // See https://stackoverflow.com/q/7473872/10980984
        $this->session->set('trickGroup', serialize($trick->getTrickGroup()));
    }

    // Reads a trick from the session.
    public function readTrickFromSession()
    {
        // Symfony remove() session method deletes a session attribute
        // and returns its value.
        $trick = unserialize($this->session->remove('trick'));
        // It seems that the trick group must be stored separately
        // and must be "merged" after being read from the session
        // to avoid "Entity passed to the choice field must be managed.
        // Maybe you forget to persist it in the entity manager?" error.
        $trickGroup = $this->getDoctrine()
            ->getManager()
            ->merge(unserialize($this->session->remove('trickGroup')));
        $trick->setTrickGroup($trickGroup);

        return $trick;
    }

    // Deletes a media from the database.
    /**
     * @ParamConverter("media")
     */
    public function deleteMediaFromDB(Media $media)
    {
        $result = [];

        try {
            $this->entityManager->remove($media);
            $this->entityManager->flush();

            $result['msg_type'] = 'success';
            $result['message'] = 'media_deleted_successfully';
            $result['dest_page'] = 'homepage';
        } catch (Exception $e) {
            $result['msg_type'] = 'danger';
            $result['message'] = $e->getMessage();
            $result['dest_page'] = 'homepage';
        }

        return $result;
    }

    public function dropUploadedFileForAllMedias(Trick $trick)
    {
        foreach ($trick->getMedias() as $media) {
            $media->emptyFile();
        }

        return $trick;
    }

    // Returns a subset of tricks with their cover image
    // for the homepage (no media).
    public function getTricksForIndexPage($limit, $offset)
    {
        $tricks = $this->entityManager->getRepository(Trick::class)
        ->findAllTricksForPagination($limit, $offset);

        $tricksArray = [];

        foreach ($tricks as $key => $trick) {
            $tricksArray[$key]['id'] = $trick->getId();
            $tricksArray[$key]['name'] = $trick->getName();
            $tricksArray[$key]['slug'] = $trick->getSlug();
            $tricksArray[$key]['coverImage'] = $this->getCoverImageByTrickId(
                $trick->getId()
            );
        }

        return $tricksArray;
    }

    // Returns an array with the medias from a trick id.
    public function getMediasArrayByTrickId($trickId)
    {
        return $this->entityManager->getRepository(Media::class)
            ->findMediasByTrickIdOrderedByFileType($trickId);
    }

    // Returns a collection with the medias from a trick id.
    public function getMediasCollectionByTrickId($trickId)
    {
        $mediasArray = $this->getMediasArrayByTrickId($trickId);

        $mediasCollection = new ArrayCollection();

        foreach ($mediasArray as $media) {
            $mediasCollection->add($media);
        }

        return $mediasCollection;
    }

    // Returns the cover image file name from a trick id.
    public function getCoverImageByTrickId($trickId)
    {
        $cover_image_details = $this->entityManager->getRepository(Media::class)
            ->findCoverImageOrDefault($trickId);

        return $cover_image_details[0]->getId() . '.'
            . $cover_image_details[0]->getFileUrl();
    }

    // Returns the group name from a trick id.
    public function getGroupNameByTrickGroupId($trickId)
    {
        return $this->entityManager->getRepository(TrickGroup::class)
            ->findGroupNameByGroupId($trickId);
    }

    // Set the new cover image (the given media) for the given trick.
    public function setTrickCover($trick, $newCoverMedia)
    {
        // First, we unset any set cover.
        self::unsetTrickCover($trick);

        // Then we set the new cover image
        $newCoverMedia->setDefaultCover(true);
        $this->entityManager->persist($newCoverMedia);

        $this->entityManager->flush();
    }

    // Unset the cover image for the given trick.
    public function unsetTrickCover($trick)
    {
        // We unset any set cover.
        $medias = $trick->getMedias();

        foreach ($medias as $media) {
            $media->setDefaultCover(false);
            $this->entityManager->persist($media);
        }

        $this->entityManager->flush();
    }
}
