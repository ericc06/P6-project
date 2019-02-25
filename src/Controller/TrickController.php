<?php

// src/Controller/TrickController.php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickType;
use App\Service\TrickManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $env = getenv('APP_ENV');

        return $this->render('index.html.twig', array(
            'nom' => $env,
        ));
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

    /**
     * Trick creation form.
     *
     * @Route("/add-trick", name="trick_add")
     */
    public function add(Request $request)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->getFlashBag()->add(
                'notice',
                $this->i18n->trans('trick_creation_done')
            );
            return $this->redirectToRoute('homepage');
        }

        return $this->render('trick/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
