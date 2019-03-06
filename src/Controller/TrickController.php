<?php

// src/Controller/TrickController.php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Form\TrickType;
use App\Service\TrickManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class TrickController extends Controller
{
    private $trickManager;

    public function __construct(
        TrickManager $trickManager,
        TranslatorInterface $translator
    ) {
        $this->trickManager = $trickManager;
        $this->i18n = $translator;
    }
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
     * @Route("/tricks/{id}", name="trick_view", requirements={"id"="\d+"}, methods={"GET"})
     * @ParamConverter("trick")
     */
    public function view(Trick $trick)
    {
        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array('trick' => $trick));
        return new Response($content);
        //return new Response("test");
    }

    /**
     * Trick creation form.
     *
     * @Route("/tricks", name="trick_add", methods={"GET","POST"})
     */
    public function add(Request $request)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                'notice',
                $this->i18n->trans('trick_creation_done')
            );
            return $this->redirectToRoute('homepage');
        }

        return $this->render('trick/add.html.twig', array(
            'form' => $form->createView(),
            'trick' => $trick,
        ));
    }

    /**
     * Trick update form.
     *
     * @Route("/tricks/{id}/edit", name="trick_update", methods={"GET","PUT"})
     */
    public function edit(Request $request)
    {
        // Récupération d'une figure déjà existante, d'id $id.
        $trick = $this->trickManager->getTrickById($request->get('id'));

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                'notice',
                $this->i18n->trans('trick_creation_done')
            );
            return $this->redirectToRoute('homepage');
        }

        return $this->render('trick/update.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    /**
     * @Route("/tricks/{id}/delete", name="trick_delete", requirements={"id"="\d+"})
     */
    public function delete(Trick $trick)
    {
        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array('trick' => $trick));
        return new Response($content);
        //return new Response("test");
    }
}
