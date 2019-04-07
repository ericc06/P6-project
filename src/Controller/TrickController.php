<?php
// src/Controller/TrickController.php
namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\Message;
use App\Form\MessageType;
use App\Form\TrickType;
use App\Service\TrickManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TrickController extends Controller
{
    private $trickManager;
    private $i18n;
    private $session;
    private $homeTricksLoadLimit;
    private $trickPageMsgLimit;

    public function __construct(
        TrickManager $trickManager,
        TranslatorInterface $translator,
        SessionInterface $session,
        Int $homeTricksLoadLimit,
        Int $trickPageMsgLimit
    ) {
        $this->trickManager = $trickManager;
        $this->i18n = $translator;
        $this->session = $session;
        $this->homeTricksLoadLimit = $homeTricksLoadLimit;
        $this->trickPageMsgLimit = $trickPageMsgLimit;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $env = getenv('APP_ENV');

        $tricksArray = $this->trickManager
            ->getTricksForIndexPage($this->homeTricksLoadLimit, 0);

        $totalNumberOfTricks = $this->getDoctrine()
            ->getRepository(Trick::class)
            ->getTricksNumber();

        return $this->render('index.html.twig', [
            'env_name' => $env,
            'tricksArray' => $tricksArray,
            'totalNumberOfTricks' => $totalNumberOfTricks,
            'numberOfLoadedTricks' => $this->homeTricksLoadLimit
        ]);
    }

    /**
     * @Route(
     *     "/load-tricks/{limit}/{offset}",
     *     name="load_tricks",
     *     requirements={"limit":"\d+","offset":"\d+"},
     *     methods={"GET"}
     * )
     */
    public function getTricksHtmlBlock($limit = null, $offset = 0)
    {
        $tricksArray = $this->trickManager
            ->getTricksForIndexPage($limit, $offset);

        return $this->render('trick/tricksBlock.html.twig', [
            'tricksArray' => $tricksArray
        ]);
    }

    /**
     * Trick creation form.
     *
     * @Route("/tricks/new", name="trick_new", methods={"GET","POST"})
     */
    public function add(Request $request)
    {
        if (null !== $this->session->get('trick')
            && null !== $this->session->get('trickGroup')) {
            $trick = $this->trickManager->readTrickFromSession();
        } else {
            $trick = new Trick();
        }

        $form = $this->createForm(
            TrickType::class,
            $trick,
            ['validation_groups' => 'media_creation']
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->trickManager->saveTrickToDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans(
                    $result['message'],
                    $result['message_params']
                )
            );

            if (isset($result['trick'])) {
                $this->trickManager->storeTrickInSession($result['trick']);
            }
            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/add.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]);
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

        $cover_image_file = $this->trickManager
            ->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager
            ->getGroupNameByTrickGroupId($trick->getTrickGroup());

        $messagesArray = $this->getDoctrine()->getRepository(Message::class)
            ->findAllMessagesForPagination($this->trickPageMsgLimit, 0);

        $message_form = $this->createForm(MessageType::class);

        $totalNumberOfMsg = $this->getDoctrine()
            ->getRepository(Message::class)
            ->getMessagesNumber();

        return $this->render('trick/view.html.twig', [
            'trick' => $trick,
            'medias' => $medias,
            'cover_image' => $cover_image_file,
            'group_name' => $group_name,
            'messagesArray' => $messagesArray,
            'message_form' => $message_form->createView(),
            'totalNumberOfMsg' => $totalNumberOfMsg,
            'numberOfLoadedMsg' => $this->trickPageMsgLimit
        ]);
    }

    /**
     * @Route(
     *     "/load-messages/{limit}/{offset}",
     *     name="load_messages",
     *     requirements={"limit":"\d+","offset":"\d+"},
     *     methods={"GET"})
     */
    public function getMessagesHtmlBlock($limit = null, $offset = 0)
    {
        $messagesArray = $this->getDoctrine()->getRepository(Message::class)
            ->findAllMessagesForPagination($limit, $offset);

        return $this->render('trick/messagesBlock.html.twig', [
            'messagesArray' => $messagesArray
        ]);
    }

    /**
     * Message creation form.
     *
     * @Route("/messages/new", name="message_new", methods={"GET","POST"})
     */
    public function addMessage(Request $request)
    {
        if (null !== $this->session->get('message')) {
            $message = $this->trickManager->readMessageFromSession();
        } else {
            $message = new Message();
            $message->setDate(new \Datetime());
            $message->setUser($this->get('security.token_storage')
                ->getToken()
                ->getUser());
            $message->setTrick(null);
        }

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->trickManager->saveMessageToDB($message);

            // In case of error, we store the message content to the session
            // to be able to initialize the form with it.
            if (isset($result['forum_message'])) {
                $this->trickManager->storeMessageInSession(
                    $result['forum_message']
                );
            }
            $messagesArray = [$message];

            return $this->render('trick/messagesBlock.html.twig', [
                'messagesArray' => $messagesArray
            ]);
        }

        return $this->render('trick/messagesBlock.html.twig', [
            'messagesArray' => []
        ]);
    }

    /**
     * Trick update form.
     *
     * @Route(
     *     "/tricks/{id}/edit",
     *     name="trick_edit",
     *     methods={"GET","PUT","POST"}
     * )
     */
    public function edit(Trick $trick, Request $request)
    {
        // Récupération d'une figure déjà existante, d'id $id.
        $trick = $this->getDoctrine()
            ->getRepository(Trick::class)
            ->find($request->get('id'));

        $medias = $trick->getMedias();

        $trick->setMedias($medias);

        $cover_image_file = $this->trickManager
            ->getCoverImageByTrickId($trick->getId());

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

        return $this->render('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'cover_image' => $cover_image_file,
        ]);
    }

    /**
     * @Route(
     *     "/tricks/{id}/delete",
     *     name="trick_delete",
     *     requirements={"id"="\d+"}
     * )
     */
    public function delete(Request $request, $id)
    {
        $trick = $this->getDoctrine()
            ->getRepository(Trick::class)
            ->find($id);

        if (null === $trick) {
            throw new NotFoundHttpException(
                "La figure d'id " . $id . " n'existe pas."
            );
        }

        // On crée un formulaire vide, qui ne contiendra que le champ CSRF
        // Cela permet de protéger la suppression d'annonce contre cette faille
        $form = $this->get('form.factory')->create();

        if ($request->isMethod('POST')
            && $form->handleRequest($request)->isValid()) {
            $result = $this->trickManager->deleteTrickFromDB($trick);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );

            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('trick/delete.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *      "/tricks/{id}/medias/{mediaId}/delete",
     *      name="media_delete",
     *      requirements={"id":"\d+","mediaId":"\d+"},
     *      methods={"GET","POST","DELETE"},
     *      condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter(
     *     "media",
     *     class="App\Entity\Media",
     *     options={"mapping": {"mediaId": "id"}}
     * )
     */
    public function deleteMedia(Media $media, Request $request)
    {
        if ($request->isMethod('POST')
            && ($this->isCsrfTokenValid(
                'delete_media_tk',
                $request->request->get('token')
            ))
        ) {
            $mediaId = $media->getId();
            $this->trickManager->deleteMediaFromDB($media);

            return new Response('{"id":' . $mediaId . '}');
        }

        return new JsonResponse(array('message' => 'Error'), 400);
    }

    // id & mediaId are all numeric, or respectively strictly equal to
    // "TRICK" and "MEDIA" if used as placeholders (see "edit.html.twig").
    /**
     * @Route(
     *      "/tricks/{id}/medias/{mediaId}/set_cover",
     *      name="set_cover",
     *      requirements={"id":"\d+|TRICK","mediaId":"\d+|MEDIA"},
     *      methods={"GET","POST","DELETE"},
     *      condition="request.isXmlHttpRequest()"
     * )
     * @ParamConverter(
     *      "media",
     *      class="App\Entity\Media",
     *      options={"mapping": {"mediaId": "id"}}
     * )
     */
    public function setCover(Trick $trick, Media $media, Request $request)
    {
        if ($request->isMethod('POST')
            && ($this->isCsrfTokenValid(
                'update_cover_tk',
                $request->request->get('token')
            ))
        ) {
            $this->trickManager->setTrickCover($trick, $media);

            return new Response('{"id":' . $media->getId()
                . ', "extension": "' . $media->getFileUrl() . '"}');
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    /**
     * @Route(
     *      "/tricks/{id}/unset_cover",
     *      name="unset_cover",
     *      requirements={"id":"\d+"},
     *      methods={"GET","POST","DELETE"},
     *      condition="request.isXmlHttpRequest()"
     * )
     */
    public function unsetCover(Trick $trick, Request $request)
    {
        if ($request->isMethod('POST')
            && ($this->isCsrfTokenValid(
                'unset_cover_tk',
                $request->request->get('token')
            ))
        ) {
            $this->trickManager->unsetTrickCover($trick);

            $defaultCover = $this->trickManager
                ->getCoverImageByTrickId($trick->getId());

            // Returns image file name with extension.
            return new Response('{"coverFile": "' . $defaultCover . '"}');
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }
}
