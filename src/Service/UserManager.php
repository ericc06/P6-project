<?php
// src/Service/UserManager.php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
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
        $this->em = $this->getDoctrine()->getManager();
    }

    public function saveUserToDB(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function deleteUserFromDB(User $user)
    {
        $this->em->remove($user);
        $this->em->flush();
    }


    public function persistUser(User $user, Request $request)
    {
        $this->logger->info('IN persistUser  <<<<<<<<<<<<');

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        // Recherche d'un compte utilisateur à partir de l'adresse e-mail
        // pour éviter de créer le compte une seconde fois.
        $userAccountAlreadyExists = ($userRepository->count(['email' => $user->getEmail()]) === 0) ? false : true;

        if (false === $userAccountAlreadyExists) {
            $encoded = $this->encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($encoded);

            $user->setIsActiveAccount(false);
            $user->setActivationToken(random_int(1000000000, 9999999999));
            $user->setRoles(["ROLE_USER"]);

            // On enregistre notre objet $user dans la base de données
            $this->saveUserToDB($user);
 
            return true;
        } else {
            $request->getSession()->getFlashBag()->add('notice', "Un compte existe déjà avec cet e-mail. Merci de vous connecter.");

            return false;
        }
    }

    public function sendValidationEmail(User $user, Request $request)
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
            ->setBody(
                $this->renderView(
                    'emails/registration.html.twig',
                    [
                        'userName' => $user->getUsername(),
                        'validationUrl' => $validation_url,
                    ]
                ),
                'text/html'
            );

        $result = $this->mailer->send($message);

        //$result = 0;

        if (0 !== $result) {
            $request->getSession()->getFlashBag()->add('notice', "Un email de vérification vous a été envoyé. Merci de le consulter.");

            return true;
        } else {
            // En cas d'échec d'envoi du mail de vérification, on supprime le compte
            // pour permettre à l'utilisateur de le recréer pour renvoyer le mail.
            $userToDelete = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($user->getEmail());

            if ($userToDelete) {
                $this->deleteUserFromDB($user);
            }

            $request->getSession()->getFlashBag()->add('error', "Une erreur est survenue lors de l'envoi du mail de confirmation. Merci de réessayer dans quelques instants.");

            return false;
        }
    }

    public function confirmUserRegistration(Request $request)
    {
        // First we need to check the email address and token consistency.

        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($request->query->get('m'));

        $urlToken = $request->query->get('t');

        if ($urlToken === $user->getActivationToken()) {
            $user->setIsActiveAccount(true);
            $this->saveUserToDB($user);
        }

        if (true === $user->getIsActiveAccount()) {
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
