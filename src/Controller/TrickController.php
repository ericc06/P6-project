<?php

// src/Controller/TrickController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $env = getenv('APP_ENV');
        $content = $this
            ->get('templating')
            ->render('trick/index.html.twig', array('nom' => $env));
        return new Response($content);
        //return new Response("test");
    }

    /**
     * @Route("/trick/{trick_name}", name="trick_view", requirements={"trick_name"="\w+"})
     */
    public function view($trick_name)
    {
        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array('nom' => $trick_name));
        return new Response($content);
        //return new Response("test");
    }
}
