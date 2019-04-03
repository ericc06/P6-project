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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TrickController extends Controller
{
    private $trickManager;
    private $session;

    public function __construct(
        TrickManager $trickManager,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        SessionInterface $session
    ) {
        $this->trickManager = $trickManager;
        $this->i18n = $translator;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $env = getenv('APP_ENV');

        $tricksArray = $this->trickManager->getAllTricksForIndexPage();

        return $this->render('index.html.twig', array(
            'env_name' => $env,
            'tricksArray' => $tricksArray,
        ));
    }

    /**
     * Trick creation form.
     *
     * @Route("/tricks/new", name="trick_new", methods={"GET","POST"})
     */
    public function add(Request $request)
    {
        if (null !== $this->session->get('trick') && null !== $this->session->get('trickGroup')) {
            $trick = $this->trickManager->readTrickFromSession();
        } else {
            $trick = new Trick();
        }

        $form = $this->createForm(TrickType::class, $trick, ['validation_groups' => 'media_creation']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'], $result['message_params'])
            );

            if (isset($result['trick'])) {
                $this->trickManager->storeTrickInSession($result['trick']);
            }
            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/add.html.twig', array(
            'form' => $form->createView(),
            'trick' => $trick,
        ));
    }

    /**
     * @Route("/tricks/{id}-{slug}",
     *  name="trick_show",
     *  requirements={"id"="\d+", "slug"="[\w-]+"},
     *  methods={"GET"})
     * @ParamConverter("trick")
     */
    public function show(Trick $trick)
    {
        $medias = $this->trickManager->getMediasArrayByTrickId($trick->getId());

        $cover_image_file = $this->trickManager->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager->getGroupNameByTrickGroupId($trick->getTrickGroup());

        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array(
                'trick' => $trick,
                'medias' => $medias,
                'cover_image' => $cover_image_file,
                'group_name' => $group_name,
            ));
        return new Response($content);
    }

    /**
     * Trick update form.
     *
     * @Route("/tricks/{id}/edit", name="trick_edit", methods={"GET","PUT","POST"})
     * // @ParamConverter("trick")   // Using custom TrickParamConverter
     */
    public function edit(Trick $trick, Request $request)
    {
        // Récupération d'une figure déjà existante, d'id $id.
        //$trick = $this->getDoctrine()->getRepository(Trick::class)->find($request->get('id'));

        //\var_dump($trick->getMedias());
        //\var_dump($trick->getMedias());
        //\var_dump($trick->getMedias()[0]);
        

        //$medias = $this->trickManager->getMediasArrayByTrickId($trick->getId());
        $medias = $trick->getMedias();

        $trick->setMedias($medias);
        //\var_dump($trick->getMedias());

        $cover_image_file = $this->trickManager->getCoverImageByTrickId($trick->getId());

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
            //'medias' => $medias,
            'cover_image' => $cover_image_file,
        ));
    }

    /**
     * @Route("/tricks/{id}/delete", name="trick_delete", requirements={"id"="\d+"})
     */
    public function delete(Request $request, $id)
    {
        $trick = $this->getDoctrine()->getRepository(Trick::class)->find($id);

        if (null === $trick) {
            throw new NotFoundHttpException("La figure d'id " . $id . " n'existe pas.");
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $result = $this->trickManager->deleteTrickFromDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );

            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/delete.html.twig', array(
            'trick' => $trick,
            'form' => $form->createView(),
        ));
    }

//          * @ParamConverter("trick", class="App\Entity\Trick", options={"mapping": {"trickId": "id"}})
//      * @ParamConverter("trick")

    /**
     * @Route(
     *      "/tricks/{id}/medias/{mediaId}/delete",
     *      name="media_delete",
     *      requirements={"id":"\d+","mediaId":"\d+"},
     *      methods={"GET","POST","DELETE"},
     *      condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter("media", class="App\Entity\Media", options={"mapping": {"mediaId": "id"}})
     */
    public function deleteMedia(Media $media, Request $request)
    {
        //$trick->setMedias($this->trickManager->getMediasArrayByTrickId($trick->getId()));
        $this->logger->info('> > > > > > IN deleteMedia  < < < < < <'. \serialize($media));

        $mediaId = $media->getId();
        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        $this->logger->info('>>>>>>>>>>>>> IN deleteMedia  < < < < < <'. $request->request->get('token'));
        
        if ($request->isMethod('POST')
            && ($this->isCsrfTokenValid('delete_media_tk', $request->request->get('token')))
            //&& $form->handleRequest($request)->isSubmitted()
            //&& $form->handleRequest($request)->isValid()
        ) {
        //if ($request->isMethod('POST')) {
            $this->logger->info('> > > > > > Before deleteMediaFromDB  < < < < < <'. \serialize($media));
            $result = $this->trickManager->deleteMediaFromDB($media);
            $this->logger->info('> > > > > > After deleteMediaFromDB  < < < < < <'. \serialize($media));

            return new Response('{"id":' . $mediaId . '}');
            /*
            return new JsonResponse(array(
                'message' => 'Success!',
                'id' => $mediaId
            ), 200);
            */
        }

        $response = new JsonResponse(array('message' => 'Error'), 400);
        return $response;

//        if ($result['is_successful'] === true) {
//            return new Response('{"id":'.$media->getId().'}');
//        } else {
//            throw $this->createNotFoundException('The media couldn\'t be deleted');
        //$this->trickManager->deleteMediaFromDB($media);
        //$this->trickManager->flush();

        /*if ($request->isXmlHttpRequest()) {
            return $this->redirectToRoute('homepage', [
                'id' => $media->getId()
            ]);
        }
        */
//        }
    }
}
