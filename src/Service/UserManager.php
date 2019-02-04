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
        // S'il existe un compte encore inactif, on le supprime et on le recrée.
        // S'il existe un compte actif, on invite l'utilisateur à se connecter.

        $existingUser = $userRepository->findOneByEmail($user->getEmail());

        if ($existingUser) {
            if (!$existingUser->getIsActiveAccount()) {
                $this->deleteUserFromDB($existingUser);
                $userAccountAlreadyExists = false;
            } else {
                $userAccountAlreadyExists = true;
            }
        } else {
            $userAccountAlreadyExists = false;
        }

        //// $userAccountAlreadyExists = ($userRepository->count(['email' => $user->getEmail()]) === 0) ? false : true;

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
        ;

        $data = [
            'userName' => $user->getUsername(),
            'validationUrl' => $validation_url,
            'image_src' => $message->embed(\Swift_Image::fromPath(realpath(__DIR__ . "\\..\\..\\") . "\\public\\build\\images\\emails\\homepage-500.jpg")),
        ];

        $message->setBody(
            $this->renderView('emails/registration.html.twig', $data),
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

    public function getUserByEmail(String $email, Request $request)
    {
        $this->logger->info('> > > > > > IN userEmailExists  < < < < < <');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email);

        if (null !== $user) {
            return $user;
        } else {
            $request->getSession()->getFlashBag()->add('warning', "Il n'existe pas de compte d'utilisateur avec cette adresse e-mail. Merci de réessayer.");
            return null;
        }
    }

    public function sendPwdResetEmail(User $user, Request $request)
    {
        $this->logger->info('> > > > > > IN sendPwdResetEmail  < < < < < <');

        // TODO : Remplacer ActivationToken pas PwdResetToken

        $user->setActivationToken(random_int(1000000000, 9999999999));
        $this->persistUser($user, $request);

        $pwd_reset_url = $this->generateUrl(
            'pwd_reset_confirm',
            [
                'm' => $user->getEmail(),
                //'t' => $user->getPwdResetToken(),
                't' => $user->getActivationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new \Swift_Message("Réinitialisation de votre mot de passe."))
            ->setFrom('contact@monsite.loc')
            ->setTo('eric.codron@gmail.com')
        ;

        $data = [
            'userName' => $user->getUsername(),
            'pwdResetUrl' => $pwd_reset_url,
            'image_src' => $message->embed(\Swift_Image::fromPath(realpath(__DIR__ . "\\..\\..\\") . "\\public\\build\\images\\emails\\homepage-500.jpg")),
        ];

        $message->setBody(
            $this->renderView('emails/pwdReset.html.twig', $data),
            'text/html'
        );

        $result = $this->mailer->send($message);

        //$result = 0;

        if (0 !== $result) {
            $request->getSession()->getFlashBag()->add('notice', "Un email de réinitialisation de votre mot de passe vous a été envoyé. Merci de le consulter.");

            return true;
        } else {
            // En cas d'échec d'envoi du mail
            $request->getSession()->getFlashBag()->add('error', "Une erreur est survenue lors de l'envoi du mail de réinitialisation de votre mot de passe. Merci de réessayer dans quelques instants.");

            return false;
        }
    }

    public function confirmPwdResetEmail(Request $request)
    {
        // First we need to check the email address and reset token consistency.

        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($request->query->get('m'));

        $urlToken = $request->query->get('t');

        if ($urlToken === $user->getActivationToken()) {
            return true;
        } else {
            $request->getSession()->getFlashBag()->add('warning', "La vérification des informations de réinitialisation de votre mot de passe a échoué. Merci d'essayer de nouveau.");
            return false;
        }
    }

    public function checkNewPdw(String $pwd1, String $pwd2)
    {
        if ($pwd1 === $pwd2) {
            $request->getSession()->getFlashBag()->add('info', "Votre mot de passe a été modifié avec succès.");
            return true;
        } else {
            $request->getSession()->getFlashBag()->add('warning', "Vous avez saisi deux mots de passe différents. Merci d'essayer de nouveau.");
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
