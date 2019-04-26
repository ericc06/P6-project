<?php
// src/Service/MessageManager.php

namespace App\Service;

use App\Entity\Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class MessageManager extends Controller
{
    protected $container;
    private $session;
    private $entityManager;

    public function __construct(
        SessionInterface $session,
        Container $container
    ) {
        $this->container = $container;
        $this->session = $session;
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    // Inserts or updates a forum message into the database.
    public function saveMessageToDB(Message $message)
    {
        $result = [];

        try {
            $this->entityManager->persist($message);
            $this->entityManager->flush();

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
}
