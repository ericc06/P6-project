<?php
// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserManager;
use App\Service\PasswordManager;
use App\Service\RegistrationManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends Controller
{
    private $userManager;
    private $pwdManager;
    private $regManager;
    private $i18n;

    public function __construct(
        UserManager $userManager,
        PasswordManager $passwordManager,
        RegistrationManager $registrationManager,
        TranslatorInterface $translator
    ) {
        $this->userManager = $userManager;
        $this->pwdManager =  $passwordManager;
        $this->regManager =  $registrationManager;
        $this->i18n = $translator;
    }

    /**
     * User registration form.
     *
     * @Route("/registration", name="user_registration")
     */
    public function add(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->regManager->checkAddUser($user);

            $request->getSession()->getFlashBag()->add(
                $result['msg_type'],
                $this->i18n->trans($result['message'])
            );

            return $this->redirectToRoute($result['dest_page']);
        }

        return $this->render('user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * User account validation.
     *
     * @Route("/registration-confirm", name="registration_confirm")
     */
    public function confirmAccount(Request $request)
    {
        if (true === $this->regManager->confirmUserRegistration($request)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->i18n->trans('account_validated')
            );

            return $this->redirectToRoute('homepage');
        }

        $request->getSession()->getFlashBag()->add(
            'danger',
            $this->i18n->trans('account_validation_failed')
        );

        return $this->redirectToRoute('user_registration');
    }

    /**
     * User update form.
     *
     * @Route(
     *     "/user/{id}/edit",
     *     name="user_edit",
     *     methods={"GET","PUT","POST"}
     * )
     */
    public function edit(User $user = null, Request $request)
    {
        // If the given user id doesn't exist we display an error page.
        if ($user === null) {
            return $this->render('user/notFound.html.twig');
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $destPage = self::handleUserSubmit($user, $request);

            return $this->redirectToRoute($destPage);
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    private function handleUserSubmit(User $user, Request $request)
    {
        // $file stores the uploaded image file
        /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $user->getAvatar();

        if ($file !== null) {
            $fileName = $user->getId().'.'.$file->guessExtension();

            $user->setFileExtension($file->guessExtension());

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('avatars_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if sth happens during file upload
            }
        }

        $result = $this->userManager->saveUserToDB($user);

        $request->getSession()->getFlashBag()->add(
            $result['msg_type'],
            $this->i18n->trans($result['message'], [], 'gui')
        );
        
        return $result['dest_page'];
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

            $result = self::handleForgottenPwdSubmit($email, $form, $request);

            if (null !== $result) {
                return $result;
            }

            $request->getSession()->getFlashBag()->add(
                'danger',
                $this->i18n->trans('no_account_found_with_this_email')
            );
        }

        return $this->render('user/forgotten-pwd-step1.twig', ['form' => $form->createView()]);
    }

    private function handleForgottenPwdSubmit($email, $form, Request $request)
    {
        if (null !== $user = $this->getDoctrine()->getRepository(User::class)
            ->findOneByEmail($email)) {
            if (true === $this->pwdManager->sendPwdResetEmail($user)) {
                return $this->render('user/pwd-reset-email-sent.html.twig', []);
            }

            $request->getSession()->getFlashBag()->add(
                'danger',
                $this->i18n->trans('error_sending_pwd_reset_email')
            );

            return $this->render('user/forgotten-pwd-step1.twig', ['form' => $form->createView()]);
        }

        return null;
    }

    /**
     * Forgotten password.
     *
     * @Route("/new-pwd", name="user_new_pwd")
     */
    public function userNewPwd(Request $request)
    {
        $form = $this->createForm(FormType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return self::handleNewPwdSubmit($request, $form);
        }

        if (true === $this->pwdManager->confirmPwdResetEmail($request)) {
            return $this->render(
                'user/forgotten-pwd-step2.twig',
                [
                    'form' => $form->createView(),
                    'email' => $request->query->get('m'),
                    'token' => $request->query->get('t')
                ]
            );
        }

        $request->getSession()->getFlashBag()->add(
            'warning',
            $this->i18n->trans('pwd_reset_check_failed')
        );

        return $this->redirectToRoute('user_forgotten_pwd');
    }

    private function handleNewPwdSubmit(Request $request, $form)
    {
        if (true === $this->pwdManager->checkAndSaveNewPwd($request)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->i18n->trans('new_pwd_successfuly_saved')
            );

            return $this->redirectToRoute('user_login');
        }

        $request->getSession()->getFlashBag()->add(
            'warning',
            $this->i18n->trans('new_pwd_confirmation_failed')
        );

        return $this->render(
            'user/forgotten-pwd-step2.twig',
            [
                'form' => $form->createView(),
                'email' => $request->request->get('m'),
                'token' => $request->request->get('t')
            ]
        );
    }
}
