<?php
// src/Service/UserManager.php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserManager extends Controller
{
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        UserPasswordEncoderInterface $encoder,
        Container $container,
        \Swift_Mailer $mailer
    ) {
        $this->logger = $logger;
        $this->encoder = $encoder;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    public function persistUser(User $user)
    {
        $this->logger->info('IN persistUser  <<<<<<<<<<<<');

        // TODO: Check if user already exists!!!

        $encoded = $this->encoder->encodePassword($user, $user->getPassword());

        $user->setPassword($encoded);

        $user->setIsActiveAccount(false);
        $user->setActivationToken(random_int(1000000000, 9999999999));
        $user->setRoles(["ROLE_USER"]);

        // On enregistre notre objet $user dans la base de données
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
    }

    public function sendValidationEmail(User $user, Request $request)
    {
        //$this->logger->info('About to find a happy message!');
        //$mailer = $this->container->get('mailer');

        $validation_url = $this->generateUrl(
            'registration_confirm',
            [
                'm' => $user->getEmail(),
                't' => $user->getActivationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        //$validation_url = "http://localhost/P6-project-flex/public/confirm?m=" . $user->getEmail() . "t=" . $user->getActivationToken();

        $message = (new \Swift_Message("Demande de confirmation d'inscription"))
            ->setFrom('eric.codron@gmail.com')
            ->setTo('eric.codron@gmail.com')
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    [
                        'userName' => $user->getUsername(),
                        'validationUrl' => $validation_url,
                    ]
                ),
                'text/html'
            );

        $result = $this->mailer->send($message);

        $this->logger->info('MAIL SEND RESULT: ' . $result .' <<<<<<<<<<<<');

        /*$message = "COUCOU !";
        // Envoi du mail
        //mail($user->getEmail, "Demande de confirmation d'inscription", $message);
        mail("eric.codron@gmail.com", "Demande de confirmation d'inscription", $message);
        */

        if (0 !== $result) {
            $this->logger->info('> > > > > > > > > > > IN IF < < < < < < < < < < <');
            $request->getSession()->getFlashBag()->add('notice', "Un email de validation vous a été envoyé. Merci de le vérifier.");
            return true;
        } else {
            $this->logger->info('> > > > > > > > > > > IN ELSE < < < < < < < < < < <');
            $request->getSession()->getFlashBag()->add('error', "Une erreur est survenue lors de l'envoi du mail de confirmation. Merci de recommencer.");
            return false;
        }
    }

    public function confirmUserRegistration(Request $request)
    {
        // TODO: First we need to check the email address and token consistency...

        if (true) {
            $request->getSession()->getFlashBag()->add('notice', 'Votre compte a bien été validé. Vous pouvez vous connecter.');
            return true;
        } else {
            $request->getSession()->getFlashBag()->add('warning', 'La validation de votre compte a échoué. Merci de vous enregistrer de nouveau.');
            return false;
        }
    }

    public function initLoginForm(AuthenticationUtils $authenticationUtils)
    {
        // dernier identifiant de connexion utilisé
        $lastUsername = $authenticationUtils->getLastUsername();
        // erreur d'authentification (s'il y en a une)
        $error = $authenticationUtils->getLastAuthenticationError();

        return [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ];
    }
}
