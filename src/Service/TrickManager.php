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
    private $em;

    public function __construct(
        Container $container,
        UrlGeneratorInterface $router,
        SessionInterface $session
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->session = $session;
        $this->em = $this->getDoctrine()->getManager();
    }

    // Inserts or updates a trick into the database.
    public function saveTrickToDB(Trick $trick)
    {
        $result = [];

        try {
            $this->em->persist($trick);
            $this->em->flush();

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
                        'id' => $this->em->getRepository(Trick::class)
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

    // Inserts or updates a forum message into the database.
    public function saveMessageToDB(Message $message)
    {
        $result = [];

        try {
            $this->em->persist($message);
            $this->em->flush();

            $result['is_successful'] = true;
            $result['msg_type'] = 'success';
            $result['message'] = 'message_saved_successfully';
            $result['message_params'] = [];
            //$result['dest_page'] = 'homepage';
        } catch (Exception $e) {
            $result['is_successful'] = false;
            $result['msg_type'] = 'danger';
            $result['message'] = $e->getMessage();
            //$result['dest_page'] = 'trick_new';
            $result['forum_message'] = $message;
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
            ->getEntityManager()
            ->merge(unserialize($this->session->remove('trickGroup')));
        $trick->setTrickGroup($trickGroup);

        return $trick;
    }

    // Stores a forum message in the session.
    public function storeMessageInSession(Message $message)
    {
        $this->session->set('message', serialize($message));
    }

    // Reads a forum message from the session.
    public function readMessageFromSession()
    {
        // Symfony remove() session method deletes a session attribute
        // and returns its value.
        $message = unserialize($this->session->remove('message'));

        return $message;
    }

    // Deletes a media from the database.
    /**
     * @ParamConverter("media")
     */
    public function deleteMediaFromDB(Media $media)
    {
        $result = [];

        try {
            $this->em->remove($media);
            $this->em->flush();

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
        $tricks = $this->em->getRepository(Trick::class)
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

    // Returns a subset of messages.
    /*public function getMessagesForTrickPage($limit, $offset)
    {
        $messagesArray = $this->em->getRepository(Message::class)
        ->findAllMessagesForPagination($limit, $offset);

        return $tricksArray;
    }
    */

    // Returns an array with the medias from a trick id.
    public function getMediasArrayByTrickId($id)
    {
        return $this->em->getRepository(Media::class)
            ->findMediasByTrickIdOrderedByFileType($id);
    }

    // Returns a collection with the medias from a trick id.
    public function getMediasCollectionByTrickId($id)
    {
        $mediasArray = $this->getMediasArrayByTrickId($id);

        $mediasCollection = new ArrayCollection();

        foreach ($mediasArray as $media) {
            $mediasCollection->add($media);
        }

        return $mediasCollection;
    }

    // Returns the cover image file name from a trick id.
    public function getCoverImageByTrickId($id)
    {
        $cover_image_details = $this->em->getRepository(Media::class)
            ->findCoverImageOrDefault($id);

        return $cover_image_details[0]->getId() . '.'
            . $cover_image_details[0]->getFileUrl();
    }

    // Returns the group name from a trick id.
    public function getGroupNameByTrickGroupId($id)
    {
        return $this->em->getRepository(TrickGroup::class)
            ->findGroupNameByGroupId($id);
    }

    // Set the new cover image (the given media) for the given trick.
    public function setTrickCover($trick, $newCoverMedia)
    {
        // First, we unset any set cover.
        $medias = $trick->getMedias();

        foreach ($medias as $media) {
            $media->setDefaultCover(false);
            $this->em->persist($media);
        }

        // Then we set the new cover image
        $newCoverMedia->setDefaultCover(true);
        $this->em->persist($newCoverMedia);

        $this->em->flush();
    }

    // Unset the cover image for the given trick.
    public function unsetTrickCover($trick)
    {
        // We unset any set cover.
        $medias = $trick->getMedias();

        foreach ($medias as $media) {
            $media->setDefaultCover(false);
            $this->em->persist($media);
        }

        $this->em->flush();
    }
}
