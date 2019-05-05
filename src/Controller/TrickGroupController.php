<?php
// src/Controller/TrickGroupController.php
namespace App\Controller;

use App\Entity\TrickGroup;
use App\Service\TrickGroupManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class TrickGroupController extends Controller
{
    private $trickGroupManager;
    private $i18n;

    public function __construct(
        trickGroupManager $trickGroupManager,
        TranslatorInterface $translator
    ) {
        $this->trickGroupManager = $trickGroupManager;
        $this->i18n = $translator;
    }

    /**
     * @Route("/trickgroups/init-list", name="init_groups_list")
     */
    public function init(Request $request)
    {
        $result = $this->trickGroupManager->initGroupsList();

        $request->getSession()->getFlashBag()->add(
            $result['msg_type'],
            $this->i18n->trans($result['message'])
        );

        return $this->redirectToRoute($result['dest_page']);
    }
}
