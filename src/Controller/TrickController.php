<?php
// src/Controller/TrickController.php
namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\Message;
use App\Form\MessageType;
use App\Form\TrickType;
use App\Service\TrickManager;
use App\Service\MessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

class TrickController extends Controller
{
    private $trickManager;
    private $messageManager;
    private $i18n;
    private $session;
    private $homeTricksInitNbr;
    private $homeTricksLoadLimit;
    private $trickPageMsgLimit;

    public function __construct(
        TrickManager $trickManager,
        MessageManager $messageManager,
        TranslatorInterface $translator,
        SessionInterface $session,
        $homeTricksInitNbr,
        $homeTricksLoadLimit,
        $trickPageMsgLimit
    ) {
        $this->trickManager = $trickManager;
        $this->messageManager = $messageManager;
        $this->i18n = $translator;
        $this->session = $session;
        $this->homeTricksInitNbr = $homeTricksInitNbr;
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
            ->getTricksForIndexPage($this->homeTricksInitNbr, 0);

        $totalNumberOfTricks = $this->getDoctrine()
            ->getRepository(Trick::class)
            ->getTricksNumber();

        return $this->render('index.html.twig', [
            'env_name' => $env,
            'tricksArray' => $tricksArray,
            'totalNumberOfTricks' => $totalNumberOfTricks,
            'numberOfInitialLoadedTricks' => $this->homeTricksInitNbr,
            'tricksLoadMoreLimit' => $this->homeTricksLoadLimit
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
    public function show(Trick $trick = null)
    {
        // If the given trick id doesn't exist we display an error page.
        if ($trick === null) {
            return $this->render('trick/notFound.html.twig');
        }

        $medias = $this->trickManager->getMediasArrayByTrickId($trick->getId());

        $cover_image_file = $this->trickManager
            ->getCoverImageByTrickId($trick->getId());

        $group_name = $this->trickManager
            ->getGroupNameByTrickGroupId($trick->getTrickGroup());

        $messagesArray = $this->getDoctrine()->getRepository(Message::class)
            ->findTrickMessagesForPagination(
                $trick->getId(),
                $this->trickPageMsgLimit,
                0
            );

        $message_form = $this->createForm(MessageType::class);

        $nbrOfMsgForTrick = $this->getDoctrine()
            ->getRepository(Message::class)
            ->getMessagesNumberForTrick($trick->getId());

        return $this->render('trick/view.html.twig', [
            'trick' => $trick,
            'medias' => $medias,
            'cover_image' => $cover_image_file,
            'group_name' => $group_name,
            'messagesArray' => $messagesArray,
            'message_form' => $message_form->createView(),
            'nbrOfMsgForTrick' => $nbrOfMsgForTrick,
            'nbrOfInitDisplMsg' => $this->trickPageMsgLimit,
            'numberOfLoadedMsg' => $this->trickPageMsgLimit
        ]);
    }

    /**
     * @Route(
     *     "/trick/{id}/load-messages/{limit}/{offset}",
     *     name="load_messages",
     *     requirements={"limit":"\d+","offset":"\d+"},
     *     methods={"GET"})
     * @ParamConverter("trick")
     */
    public function getMessagesHtmlBlock(Trick $trick, $limit = null, $offset = 0)
    {
        $messagesArray = $this->getDoctrine()->getRepository(Message::class)
            ->findTrickMessagesForPagination($trick, $limit, $offset);

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
            $message = $this->messageManager->readMessageFromSession();
        } else {
            $message = new Message();
            $message->setDate(new \Datetime());
            $message->setUser($this->get('security.token_storage')
                ->getToken()
                ->getUser());
            $trick = $this->getDoctrine()
                ->getRepository(Trick::class)
                ->find($request->request->get('trick-id'));
            $message->setTrick($trick);
        }

        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->messageManager->saveMessageToDB($message);

            // In case of error, we store the message content to the session
            // to be able to initialize the form with it.
            if (isset($result['forum_message'])) {
                $this->messageManager->storeMessageInSession(
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
    public function edit(Trick $trick = null, Request $request)
    {
        // If the given trick id doesn't exist we display an error page.
        if ($trick === null) {
            return $this->render('trick/notFound.html.twig');
        }

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
     *      "/tricks/{id}/ajax-delete",
     *      name="trick_delete_ajax",
     *      requirements={"id":"\d+"},
     *      methods={"GET","POST","DELETE"},
     *      condition="request.isXmlHttpRequest()"
     * )
     */
    public function deleteTrickAjax(Trick $trick, Request $request)
    {
        if ($request->isMethod('POST')
            && ($this->isCsrfTokenValid(
                'delete_trick_tk',
                $request->request->get('token')
            ))
        ) {
            $trickId = $trick->getId();
            $this->trickManager->deleteTrickFromDB($trick);

            // Returns deleted trick id.
            return new Response('{"id":' . $trickId . '}');
        }

        return new JsonResponse(['message' => 'Error'], 400);
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
            $trick = $media->getTrick();
            $this->trickManager->deleteMediaFromDB($media);

            //return new Response('{"id":' . $mediaId . '}');

            // In case the delete media was used as the cover image
            // we return the cover image to update the trick edition
            // page header image. Of course, it can be the same image
            // then previouly.
            $defaultCover = $this->trickManager
                ->getCoverImageByTrickId($trick->getId());

            // Returns image file name including extension.
            return new Response('{"id":' . $mediaId
                . ', "coverFile": "' . $defaultCover . '"}');
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }

    // id & mediaId are all numeric, or respectively strictly equal to
    // "TRICK" and "MEDIA" if used as placeholders (see "edit.html.twig").
    /**
     * @Route(
     *      "/tricks/{id}/medias/{mediaId}/set-cover",
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
     *      "/tricks/{id}/unset-cover",
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

            // Returns image file name including extension.
            return new Response('{"coverFile": "' . $defaultCover . '"}');
        }

        return new JsonResponse(['message' => 'Error'], 400);
    }
}
