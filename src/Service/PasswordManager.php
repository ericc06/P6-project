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

//use Symfony\Component\Validator\Constraints\DateTime;

class PasswordManager extends Controller
{
    protected $container;
    private $userManager;
    private $encoder;
    private $i18n;
    private $tools;
    private $mailer;
    private $entityManager;

    public function __construct(
        Container $container,
        UserManager $userManager,
        UserPasswordEncoderInterface $encoder,
        TranslatorInterface $translator,
        Tools $tools,
        \Swift_Mailer $mailer
    ) {
        $this->container = $container;
        $this->userManager = $userManager;
        $this->encoder = $encoder;
        $this->i18n = $translator;
        $this->tools = $tools;
        $this->mailer = $mailer;
        $this->entityManager = $this->getDoctrine()->getManager();
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
        $user->setPwdResetToken($this->tools->generateToken());
        // Setting the token creation date to now (default for Datetime()).
        $user->setPwdTokenCreationDate(new \DateTime('now'));

        $this->userManager->saveUserToDB($user);

        $pwd_reset_url = $this->generateUrl(
            'user_new_pwd',
            [
                'm' => $user->getEmail(),
                't' => $user->getPwdResetToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message_subject = $this->i18n->trans(
            'pwd_reinitialization',
            [],
            'emails'
        );

        $message = (new \Swift_Message($message_subject))
            ->setFrom('contact@monsite.loc')
            ->setTo('eric.codron@gmail.com')
        ;

        $logo_path = realpath(__DIR__ . "\\..\\..\\")
            . "\\public\\build\\images\\logo.png";

        $img_path = realpath(__DIR__ . "\\..\\..\\")
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

        $result = $this->mailer->send($message);

        if (0 !== $result) {
            return true;
        }

        return false;
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

        $user = $this->getDoctrine()
            ->getRepository(User::class)
                ->findOneByEmail($email);

        $urlToken = $request->query->get('t');

        $dateNow = new \DateTime();
        $tokenDate = $user->getPwdTokenCreationDate();
        $dateDiffSecs = $dateNow->getTimestamp() - $tokenDate->getTimestamp();

        // The token validity duration in 10 minutes (600 seconds).
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
    public function checkAndSaveNewPwd(
        String $email,
        String $token,
        Request $request
    ) {

        // TODO: Check that pwd length is < 4096 haracters
        // (https://symfony.com/doc/4.0/security/password_encoding.html)

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
            $this->userManager->saveUserToDB($user);
            return true;
        }

        return false;
    }
}
