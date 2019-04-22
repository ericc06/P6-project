<?php
// src/Service/RegistrationManager.php
namespace App\Service;

use App\Entity\User;
use App\Utils\Tools;
use App\Service\PasswordManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//use Symfony\Component\Validator\Constraints\DateTime;

class RegistrationManager extends Controller
{
    protected $container;
    private $userManager;
    private $tools;
    private $mailer;
    private $encoder;

    public function __construct(
        Container $container,
        UserManager $userManager,
        Tools $tools,
        \Swift_Mailer $mailer,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->container = $container;
        $this->userManager = $userManager;
        $this->tools = $tools;
        $this->mailer = $mailer;
        $this->encoder = $encoder;
    }

    // Returns an encoded user password.
    public function encodePwd(User $user, String $pwd)
    {
        return $this->encoder->encodePassword($user, $pwd);
    }

    // Inserts a non existing (or still inactive) user account
    // into the database.
    // If an inactive account exists for the given email,
    // it is deleted end recreated.
    // Returns true if an account has been (re)created.
    // Returns false if an active account alresady exists.
    public function persistUserRegistration(User $user)
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        // Recherche d'un compte utilisateur à partir de l'adresse e-mail
        // pour éviter de créer le compte une seconde fois.
        // On vérifie aussi l'unicité du nom d'utilisateur (username).
        // S'il existe un compte encore inactif, on le supprime et on le recrée.
        // S'il existe un compte actif, on invite l'utilisateur à se connecter.

        $existingAccount = $userRepository->findOneByEmail($user->getEmail());

        if ($existingAccount) {
            if (!$existingAccount->getIsActiveAccount()) {
                $this->userManager->deleteUserFromDB($existingAccount);
            } else {
                throw new Exception('account_already_exists', 1);
            }
        } elseif ($userRepository->findOneByUsername($user->getUsername())) {
            throw new Exception('username_already_exists', 2);
        }

        /*$user->setPassword(PasswordManager::encodePwd(
            $user,
            $user->getPassword())
        );
        */
        //$tools = $this->container->get('tools');

        $user->setPassword($this->encodePwd($user, $user->getPassword()));
        $user->setIsActiveAccount(false);
        $user->setActivationToken($this->tools->generateToken());
        $user->setRoles(["ROLE_USER"]);

        $this->userManager->saveUserToDB($user);

        return true;
    }

    // Sends the user account creation verification email to the user.
    // Returns true if the email was successfully sent.
    // Returns false if the email could not be sent.
    public function sendValidationEmail(User $user)
    {
        $validation_url = $this->generateUrl(
            'registration_confirm',
            [
                'm' => $user->getEmail(),
                't' => $user->getActivationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new \Swift_Message("Demande de confirmation d'inscription"))
            ->setFrom('contact@monsite.loc')
            ->setTo('eric.codron@gmail.com')
        ;

        $data = [
            'userName' => $user->getUsername(),
            'validationUrl' => $validation_url,
            'logo_src' => $message
                ->embed(\Swift_Image::fromPath(realpath(__DIR__ . "\\..\\..\\")
                . "\\public\\build\\images\\logo.png")),
            'image_src' => $message
                ->embed(\Swift_Image::fromPath(realpath(__DIR__ . "\\..\\..\\")
                . "\\public\\build\\images\\emails\\homepage-500.jpg")),
        ];

        $message->setBody(
            $this->renderView('emails/registration.html.twig', $data),
            'text/html'
        );

        $result = $this->mailer->send($message);

        if (0 !== $result) {
            return true;
        }

        // En cas d'échec d'envoi du mail de vérification,
        // on supprime le compte pour permettre à l'utilisateur
        // de le recréer pour renvoyer le mail.
        $userToDelete = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneByEmail($user->getEmail())
        ;

        if ($userToDelete) {
            $this->userManager->deleteUserFromDB($user);
        }
        throw new Exception('error_sending_confirmation_email');
    }

    // Performs the actions required after a user add form submission.
    // Returns an array with the relevant pieces of information.
    public function checkAddUser(User $user)
    {
        $result = [];

        try {
            self::persistUserRegistration($user);
        } catch (Exception $e) {
            switch ($e->getCode()) {
                // account_already_exists
                case 1:
                    $result['msg_type'] = 'primary';
                    $result['message'] = $e->getMessage();
                    $result['dest_page'] = 'user_login';
                    break;
                // username_already_exists
                case 2:
                    $result['msg_type'] = 'danger';
                    $result['message'] = $e->getMessage();
                    $result['dest_page'] = 'user_registration';
                    break;
                default:
                    break;
            }
            return $result;
        }

        try {
            self::sendValidationEmail($user);
            $result['msg_type'] = 'success';
            $result['message'] = 'account_creation_check_email_sent';
            $result['dest_page'] = 'homepage';
        } catch (Exception $e) {
            $result['msg_type'] = 'danger';
            $result['message'] = $e->getMessage();
            $result['dest_page'] = 'user_registration';
        }

        return $result;
    }

    // Called when a user clicks the link in the account creation
    // verification email.
    // Checks the email address and token consistency. Activates
    // the user account if OK.
    // Returns true if the verification is successful.
    // Returns false if the verification fails.
    public function confirmUserRegistration(Request $request)
    {
        // First we need to check the email address and token consistency.

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneByEmail($request->query->get('m'))
        ;

        $urlToken = $request->query->get('t');

        if ($urlToken === $user->getActivationToken()) {
            $user->setIsActiveAccount(true);
            $this->userManager->saveUserToDB($user);
        }

        if (true === $user->getIsActiveAccount()) {
            return true;
        }

        return false;
    }
}
