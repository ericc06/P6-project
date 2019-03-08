<?php

// src/Controller/TrickController.php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\TrickGroup;
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

        $tricksArray = $this->trickManager->getAllTricksForIndexPage();

        return $this->render('index.html.twig', array(
            'nom' => $env,
            'tricksArray' => $tricksArray
        ));
    }
    
    /**
     * Trick creation form.
     *
     * @Route("/tricks/new", name="trick_new", methods={"GET","POST"})
     */
    public function add(Request $request)
    {
        $trick = new Trick();

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );
            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/add.html.twig', array(
            'form' => $form->createView(),
            'trick' => $trick,
        ));
    }

    /**
     * @Route("/tricks/{slug}", name="trick_show", requirements={"slug"="[\w-]+"}, methods={"GET"})
     * @ParamConverter("trick")
     */
    public function show(Trick $trick)
    {
        //dump($trick);
        $medias = $this->trickManager->getMediasByTrickId($trick->getId());
        //$trick->setMedias($medias);
        //dump($trick);

        $cover_image_file = $this->trickManager->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager->getGroupByTrickGroupId($trick->getTrickGroup());

        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array(
                'trick' => $trick,
                'medias' => $medias,
                'cover_image' => $cover_image_file,
                'group_name' => $group_name
            ));
        return new Response($content);
        //return new Response("test");
    }


    /**
     * Trick update form.
     *
     * @Route("/tricks/{id}/edit", name="trick_edit", methods={"GET","PUT","POST"})
     */
    public function edit(Request $request)
    {
        // Récupération d'une figure déjà existante, d'id $id.
        //$trick = $this->trickManager->getTrickById($request->get('id'));
        $trick = $this->trickManager->getTrickById($request->get('id'));

        $medias = $this->trickManager->getMediasByTrickId($trick->getId());

        $cover_image_file = $this->trickManager->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager->getGroupByTrickGroupId($trick->getTrickGroup());

        $form = $this->createForm(TrickType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );

            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/edit.html.twig', array(
            'form' => $form->createView(),
            'trick' => $trick,
            'medias' => $medias,
            'cover_image' => $cover_image_file,
            'group_name' => $group_name
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
