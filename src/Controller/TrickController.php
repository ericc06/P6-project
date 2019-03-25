<?php

// src/Controller/TrickController.php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\Message;
use App\Form\TrickType;
use App\Service\TrickManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TrickController extends Controller
{
    private $trickManager;
    private $i18n;
    private $logger;
    private $session;

    /**
     * @var int
     */
    private $homepageTricksLoadLimit;

    public function __construct(
        TrickManager $trickManager,
        LoggerInterface $logger,
        TranslatorInterface $translator,
        SessionInterface $session,
        Int $homepageTricksLoadLimit
    ) {
        $this->trickManager = $trickManager;
        $this->i18n = $translator;
        $this->logger = $logger;
        $this->session = $session;
        $this->homepageTricksLoadLimit = $homepageTricksLoadLimit;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request)
    {
        $env = getenv('APP_ENV');
        $this->logger->info('> > > > > > IN index  < < < < < <'. $this->homepageTricksLoadLimit);

        $tricksArray = $this->trickManager->getTricksForIndexPage($this->homepageTricksLoadLimit, 0);
        //$tricksArray = $this->getDoctrine()->getRepository(Trick::class)->findForPagination(0);

        $totalNumberOfTricks = $this->getDoctrine()->getRepository(Trick::class)->getTricksNumber();

        return $this->render('index.html.twig', array(
            'env_name' => $env,
            'tricksArray' => $tricksArray,
            'totalNumberOfTricks' => $totalNumberOfTricks,
            'numberOfLoadedTricks' => $this->homepageTricksLoadLimit
        ));
    }

    /**
     * @Route("/load-tricks/{limit}/{offset}", name="load_tricks", requirements={"limit":"\d+","offset":"\d+"}, methods={"GET"})
     */
    public function getTricksHtmlBlock($limit = null, $offset = 0)
    {
        $this->logger->info('> > > > > > IN loadTricks  < < < < < <'. $limit);
        $this->logger->info('> > > > > > IN loadTricks  < < < < < <'. $offset);

        $tricksArray = $this->trickManager->getTricksForIndexPage($limit, $offset);

        return $this->render('trick/tricksBlock.html.twig', array(
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
        $medias = $this->trickManager->getMediasByTrickId($trick->getId());

        $cover_image_file = $this->trickManager->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager->getGroupNameByTrickGroupId($trick->getTrickGroup());

        $messages = $this->getDoctrine()->getRepository(message::class)->findAll();

        $content = $this
            ->get('templating')
            ->render('trick/view.html.twig', array(
                'trick' => $trick,
                'medias' => $medias,
                'cover_image' => $cover_image_file,
                'group_name' => $group_name,
                'messages' => $messages,
            ));
        return new Response($content);
    }

    /**
     * Trick update form.
     *
     * @Route("/tricks/{id}/edit", name="trick_edit", methods={"GET","PUT","POST"})
     */
    public function edit(Request $request)
    {
        // Récupération d'une figure déjà existante, d'id $id.
        $trick = $this->getDoctrine()->getRepository(Trick::class)->find($request->get('id'));

        $medias = $this->trickManager->getMediasByTrickId($trick->getId());

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
            'medias' => $medias,
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

    /**
     * @Route("/tricks/{trickId}/medias/{mediaId}/delete", name="media_delete", requirements={"trickId":"\d+","mediaId":"\d+"}, methods={"GET","POST","DELETE"})
     * @ParamConverter("trick", class="App\Entity\Trick", options={"mapping": {"trickId": "id"}})
     * @ParamConverter("media", class="App\Entity\Media", options={"mapping": {"mediaId": "id"}})
     */
    public function deleteMedia(Trick $trick, Media $media, Request $request)
    {
        //$trick->setMedias($this->trickManager->getMediasByTrickId($trick->getId()));
        $this->logger->info('> > > > > > IN deleteMedia  < < < < < <'. \serialize($trick));
        $trick->removeMedia($media);
        $this->logger->info('> > > > > > APRES removeMedia  < < < < < <'. \serialize($trick));
        $result = $this->trickManager->saveTrickToDB($trick);

        $this->logger->info('> > > > > > APRES saveTrickToDB  < < < < < <'. \serialize($trick));

        if ($result['is_successful'] === true) {
            return true;
        } else {
            throw $this->createNotFoundException('The media couldn\'t be deleted');
        //$this->trickManager->deleteMediaFromDB($media);
        //$this->trickManager->flush();

        /*if ($request->isXmlHttpRequest()) {
            return $this->redirectToRoute('homepage', [
                'id' => $media->getId()
            ]);
        }
        */
        }
    }
}
