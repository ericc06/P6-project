<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends Controller
{
    private $userManager;

    public function __construct(
        UserManager $userManager,
        TranslatorInterface $translator,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->i18n = $translator;
        $this->logger = $logger;
    }

    /**
     * User registration form.
     *
     * @Route("/registration", name="user_registration")
     */
    public function add(Request $request, LoggerInterface $logger)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userManager->checkAddUser($user);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );

            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('user/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * User account validation.
     *
     * @Route("/registration-confirm", name="registration_confirm")
     */
    public function confirmAccount(Request $request)
    {
        if (true === $this->userManager->confirmUserRegistration($request)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->i18n->trans('account_validated')
            );

            return $this->redirectToRoute('homepage');
        } else {
            $request->getSession()->getFlashBag()->add(
                'danger',
                $this->i18n->trans('account_validation_failed')
            );
            return $this->redirectToRoute('user_registration');
        }
    }

    /**
     * User profile modification form.
     *
     * @Route("/edit-profile", name="user_profile_edition")
     */
    public function edit(Request $request/*,UserPasswordEncoderInterface $encoder*/)
    {
        // TODO
    }

    /**
     * @Route("/login", name="user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $data = $this->userManager->initLoginForm($authenticationUtils);

        return $this->render(
            'user/login.html.twig',
            [
                'last_username' => $data['lastUsername'],
                'error' => $data['error'],
            ]
        );
    }

    /**
     * Forgotten password.
     *
     * @Route("/forgotten-pwd", name="user_forgotten_pwd")
     */
    public function pwdForgotten(Request $request)
    {
        $form = $this->createForm(FormType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $request->request->get('email');

            if (null !== $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email)) {
                if (true === $this->userManager->sendPwdResetEmail($user)) {
                    return $this->render('user/pwd-reset-email-sent.html.twig', array());
                } else {
                    $request->getSession()->getFlashBag()->add(
                        'error',
                        $this->i18n->trans('error_sending_pwd_reset_email')
                    );

                    return $this->render('user/forgotten-pwd-step1.twig', array('form' => $form->createView()));
                }
            } else {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    $this->i18n->trans('no_account_found_with_this_email')
                );
            }
        }
        return $this->render('user/forgotten-pwd-step1.twig', array('form' => $form->createView()));
    }

    /**
     * Forgotten password.
     *
     * @Route("/new-pwd", name="user_new_pwd")
     */
    public function userNewPwd(Request $request)
    {
        //$this->logger->info('> > > > > > IN userNewPwd  < < < < < <');

        $form = $this->createForm(FormType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (true === $this->userManager->checkAndSaveNewPwd(
                $request->request->get('m'),
                $request->request->get('t'),
                $request
            )
            ) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    $this->i18n->trans('new_pwd_successfuly_saved')
                );

                return $this->redirectToRoute('user_login');
            } else {
                $request->getSession()->getFlashBag()->add(
                    'warning',
                    $this->i18n->trans('new_pwd_confirmation_failed')
                );

                return $this->render(
                    'user/forgotten-pwd-step2.twig',
                    array(
                        'form' => $form->createView(),
                        'email' => $request->request->get('m'),
                        'token' => $request->request->get('t')
                    )
                );
            }
        }

        if (true === $this->userManager->confirmPwdResetEmail($request)) {
            return $this->render(
                'user/forgotten-pwd-step2.twig',
                array(
                    'form' => $form->createView(),
                    'email' => $request->query->get('m'),
                    'token' => $request->query->get('t')
                )
            );
        } else {
            $request->getSession()->getFlashBag()->add(
                'warning',
                $this->i18n->trans('pwd_reset_check_failed')
            );

            return $this->redirectToRoute('user_forgotten_pwd');
        }
    }
}
