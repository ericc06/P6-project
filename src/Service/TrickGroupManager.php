<?php
// src/Service/TrickCroupManager.php

namespace App\Service;

use App\Entity\TrickGroup;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class TrickGroupManager extends Controller
{
    protected $container;
    private $entityManager;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    // Inserts hard-coded trick groups into the database.
    public function initGroupsList()
    {
        $groupNamesArray = [
            'Grab',
            'Rotation',
            'Flip',
            'Rotation désaxée',
            'Slide',
            'One foot trick',
            'Old School',
        ];
        $result = [];
        $errorCount = 0;

        foreach ($groupNamesArray as $groupName) {
            if ($this->entityManager->isOpen()) {
                try {
                    $group = new TrickGroup();
                    $group->setName($groupName);

                    $this->entityManager->persist($group);
                    $this->entityManager->flush();
                } catch (UniqueConstraintViolationException $e) {
                    $errorCount++;
                }
            }
        }

        if (0 === $errorCount) {
            $result['is_successful'] = true;
            $result['msg_type'] = 'success';
            $result['message'] = 'trick_groups_created_successfully';
            $result['message_domain'] = 'gui';
            $result['dest_page'] = 'homepage';
        } else {
            $result['is_successful'] = false;
            $result['msg_type'] = 'warning';
            $result['message'] = 'trick_groups_already_created';
            $result['message_domain'] = 'gui';
            $result['dest_page'] = 'homepage';
        }

        return $result;
    }
}
