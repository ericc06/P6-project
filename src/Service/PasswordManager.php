<?php
// src/Service/PasswordManager.php
namespace App\Service;

use App\Entity\User;
use App\Utils\Tools;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class PasswordManager extends Controller
{
    protected $container;
    private $encoder;
    private $i18n;
    private $mailer;

    public function __construct(
        Container $container,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator,
        \Swift_Mailer $mailer
    ) {
        $this->container = $container;
        $this->encoder = $encoder;
        $this->i18n = $translator;
        $this->mailer = $mailer;
    }

    // Returns an encoded user password.
    public function encodePwd(User $user, String $pwd)
    {
        return $this->encoder->encodePassword($user, $pwd);
    }

    // Sends a password reset email to the user.
    // Returns true if the email was successfully sent.
    // Returns false if the email could not be sent.
    public function sendPwdResetEmail(User $user)
    {
        // Generating the password reset verification token.
        $user->setPwdResetToken((new Tools)->generateToken());
        // Setting the token creation date to now (default for Datetime()).
        $user->setPwdTokenCreationDate(new \DateTime());

        (new UserManager($this->container, $this->encoder, $this->mailer))->saveUserToDB($user);

        $message = self::buildEmailMessage($user);

        if (0 !== $this->mailer->send($message)) {
            return true;
        }

        return false;
    }

    private function buildEmailMessage(User $user)
    {
        $pwd_reset_url = $this->generateUrl(
            'user_new_pwd',
            [ 'm' => $user->getEmail(), 't' => $user->getPwdResetToken() ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = (new \Swift_Message($this->i18n->trans('pwd_reinitialization', [], 'emails')))
            ->setFrom('contact@monsite.loc')
            ->setTo($user->getEmail())
        ;

        $logo_path = realpath(__DIR__ . "\\..\\..\\")
            . "\\public\\build\\images\\logo.png";
        $img_path =  realpath(__DIR__ . "\\..\\..\\")
            . "\\public\\build\\images\\emails\\homepage-500.jpg";

        $data = [
            'userName' => $user->getUsername(),
            'pwdResetUrl' => $pwd_reset_url,
            'logo_src' => $message->embed(\Swift_Image::fromPath($logo_path)),
            'image_src' => $message->embed(\Swift_Image::fromPath($img_path)),
        ];

        $message->setBody(
            $this->renderView('emails/pwdReset.html.twig', $data),
            'text/html'
        );

        return $message;
    }

    // Called when a user clicks the link in the password reset
    // request confirmation email.
    // Checks the email address and token consistency.
    // Returns true if the verification is successful.
    // Returns false if the verification fails.
    public function confirmPwdResetEmail(Request $request)
    {
        // We need to check the email address and reset token consistency.
        $email = $request->query->get('m');

        if (null === $user = $this->getDoctrine()
            ->getRepository(User::class)->findOneByEmail($email)) {
            return false;
        }

        $urlToken = $request->query->get('t');

        $dateNow = new \DateTime();
        $tokenDate = $user->getPwdTokenCreationDate();
        if (null === $tokenDate) {
            return false;
        }
        $dateDiffSecs = $dateNow->getTimestamp() - $tokenDate->getTimestamp();

        // The token validity duration is 10 minutes (600 seconds).
        if ($urlToken === $user->getPwdResetToken() &&
            $dateDiffSecs < 600) {
            return true;
        }
        return false;
    }

    // Checks that the new password and the confirmation password are the same.
    // We don't check the token duration validity anymore because the user is
    // already on the new password entry form.
    // But we delete the password reset token from the database.
    // Returns true if the verification is successful.
    // Returns false if the verification fails.
    public function checkAndSaveNewPwd(Request $request)
    {
        // TODO: Check that pwd length is < 4096 haracters
        // (https://symfony.com/doc/4.0/security/password_encoding.html)

        $email = $request->request->get('m');
        $token = $request->request->get('t');

        // For the second and last time, we check the email address
        // and token consistency.
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneByEmail($email);

        $pwd = $this->encodePwd($user, $request->get('pwd1'));

        if ($token === $user->getPwdResetToken() &&
            $request->get('pwd1') === $request->get('pwd2')) {
            $user->setPassword($pwd);
            $user->setPwdResetToken(null);
            $user->setPwdTokenCreationDate(null);
            (new UserManager($this->container, $this->encoder, $this->mailer))->saveUserToDB($user);
            return true;
        }

        return false;
    }
}
