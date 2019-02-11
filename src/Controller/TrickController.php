<?php

// src/Controller/TrickController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;

class TrickController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $env = getenv('APP_ENV');

        return $this->render('index.html.twig', array(
            'env_name' => $env
        ));
    }

    /**
     * @Route("/trick/{trick_name}", name="trick_view", requirements={"trick_name"="\w+"})
     */
    public function view($trick_name)
    {
        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array('trick_name' => $trick_name));
        return new Response($content);
        //return new Response("test");
    }
}
