<?php
// src/Controller/SecurityController.php
namespace App\Controller;

use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SecurityController extends Controller
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/login", name="user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $data = $this->userManager->initLoginForm($authenticationUtils);

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $data['lastUsername'],
                'error' => $data['error'],
            ]
        );
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    // Without this method, a 404 error occurs before the firewall is called.
    // The result is that a connected user is not detected anymore on the
    // 404 error page, and he appears disconnected.
    // See https://github.com/symfony/symfony/issues/5320#issuecomment-56401080
    // No annotation. Route configured in \config\routes.yaml
    public function pageNotFound()
    {
        throw new NotFoundHttpException();
    }
}
