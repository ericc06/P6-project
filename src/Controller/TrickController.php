<?php

// src/Controller/TrickController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $env = getenv('APP_ENV');
        $content = $this
            ->get('templating')
            ->render('trick/index.html.twig', array('nom' => $env));
        return new Response($content);
        //return new Response("test");
    }
}
