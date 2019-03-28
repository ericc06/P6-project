<?php
// src/Service/PasswordManager.php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

//use Symfony\Component\Validator\Constraints\DateTime;

class PasswordManager extends Controller
{
    private $encoder;
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

    // Returns an encoded user password.
    public function generateEncodedPwd(User $user, String $pwd)
    {
        return $this->encoder->encodePassword($user, $pwd);
    }
    
    // Sends a password reset email to the user.
    // Returns true if the email was successfully sent.
    // Returns false if the email could not be sent.
    public function sendPwdResetEmail(User $user)
    {
        $this->logger->info('> > > > > > IN sendPwdResetEmail  < < < < < <');

        // Generating the password reset verification token.
        $user->setPwdResetToken($this->generateToken());
        // Setting the token creation date to now (default for Datetime()).
        $user->setPwdTokenCreationDate(new \DateTime('now'));
        $this->saveUserToDB($user);

        $pwd_reset_url = $this->generateUrl(
            'user_new_pwd',
            [
                'm' => $user->getEmail(),
                't' => $user->getPwdResetToken(),
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

        if (0 !== $result) {
            return true;
        } else {
            return false;
        }
    }

    // Called when a user clicks the link in the password reset request confirmation email.
    // Checks the email address and token consistency.
    // Returns true if the verification is successful.
    // Returns false if the verification fails.
    public function confirmPwdResetEmail(Request $request)
    {
        // We need to check the email address and reset token consistency.

        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($request->query->get('m'));

        $urlToken = $request->query->get('t');

        $dateNow = new \DateTime('now');
        $dateDiffInSeconds = $dateNow->getTimestamp() - $user->getPwdTokenCreationDate()->getTimestamp();

        // The token validity duration in 10 minutes (600 seconds).
        if ($urlToken === $user->getPwdResetToken() &&
            $dateDiffInSeconds < 600) {
            return true;
        } else {
            return false;
        }
    }

    // Checks that the new password and the confirmation password are the same.
    // We don't check the token duration validity anymore because the user is
    // already on the new password entry form.
    // But we delete the password reset token from the database.
    // Returns true if the verification is successful.
    // Returns false if the verification fails.
    public function checkAndSaveNewPwd(String $email, String $token, Request $request)
    {

        // TODO: Check that pwd length is < 4096 haracters
        // (https://symfony.com/doc/4.0/security/password_encoding.html)

        // For the second and last time, we check the email address and token consistency.
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($email);

        if ($token === $user->getPwdResetToken() &&
            $request->get('pwd1') === $request->get('pwd2')) {
            $user->setPassword($this->generateEncodedPwd($user, $request->get('pwd1')));
            $user->setPwdResetToken(null);
            $user->setPwdTokenCreationDate(null);
            $this->saveUserToDB($user);
            return true;
        } else {
            return false;
        }
    }
}
