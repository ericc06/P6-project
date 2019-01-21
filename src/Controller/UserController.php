<?php

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends Controller
{
    /**
     * User registration form.
     *
     * @Route("/registration", name="user_registration")
     */
    public function addAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        // On crée un objet User
        $user = new User();

        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
        //->add('fullname', TextType::class)
        ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
        //->add('avatar', FileType::class, array('label' => 'Votre avatar'))
            ->add('save', SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $user contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {
                $encoded = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($encoded);

                $user->setIsActiveAccount(false);
                $user->setActivationToken(random_int(1000000000, 9999999999));

                // On enregistre notre objet $user dans la base de données
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $validation_url = "http://localhost/P6-project-flex/public/confirm?m=".$user->getEmail()."t=".$user->getActivationToken();

                // Le message
                $message = "Bonjour,\r\nVous avez demandé la création d'une compte utilisateur sur le site de référence du snowboard.\r\nPour confirmer votre inscription, veuillez cliquer sur ce lien.\r\nMerci.\r\nA très bientôt.";

                // Dans le cas où nos lignes comportent plus de 70 caractères, nous les coupons en utilisant wordwrap()
                $message = wordwrap($message, 70, "\r\n");

                // Envoi du mail
                //mail($user->getEmail, "Demande de confirmation d'inscription", $message);
                mail("eric.codron@gmail.com", "Demande de confirmation d'inscription", $message);


                $request->getSession()->getFlashBag()->add('notice', 'Un email de validation vous a été envoyé. Merci de le vérifier.');

                // On redirige vers la page d'accueil
                return $this->redirectToRoute('homepage', array());
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau

        return $this->render('user/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * User profile modification form.
     *
     * @Route("/edit-profile", name="user_profile_edition")
     */
    public function editAction(Request $request, UserPasswordEncoderInterface $encoder)
    {
        // On crée un objet User
        $user = new User();

        // On crée le FormBuilder grâce au service form factory
        $formBuilder = $this->get('form.factory')->createBuilder(FormType::class, $user);

        // On ajoute les champs de l'entité que l'on veut à notre formulaire
        $formBuilder
        //->add('fullname', TextType::class)
        ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
        //->add('avatar', FileType::class, array('label' => 'Votre avatar'))
            ->add('save', SubmitType::class)
        ;

        // À partir du formBuilder, on génère le formulaire
        $form = $formBuilder->getForm();

        // Si la requête est en POST
        if ($request->isMethod('POST')) {
            // On fait le lien Requête <-> Formulaire
            // À partir de maintenant, la variable $user contient les valeurs entrées dans le formulaire par le visiteur
            $form->handleRequest($request);

            // On vérifie que les valeurs entrées sont correctes
            if ($form->isValid()) {

                $encoded = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($encoded);

                // $file stores the uploaded image file
                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $user->getAvatar();

                $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $file->move(
                        $this->getParameter('images_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    // TODO...
                }

                // updates the 'avatar' property to store the image file name
                // instead of its contents
                $user->setAvatar($fileName);

                $user->setIsActiveAccount(false);
                $user->setActivationToken(random_int(1000000000, 9999999999));

                // On enregistre notre objet $user dans la base de données
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Un email de validation vous a été envoyé. Merci de le vérifier.');

                // On redirige vers la page d'accueil
                return $this->redirectToRoute('homepage', array());
            }
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau

        return $this->render('user/add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * @Route("/registration2", name="user_registration2")
     */
    /*
    public function registration(AuthenticationUtils $authenticationUtils): Response
    {
    // Instancier objet User
    // création de formulaire FormType
    // handle request / isvalid
    // Utilisation de l'encodeur de mdp de Symfony
    // Entitymanager

    // erreur d'authentification (s'il y en a une)
    $error = $authenticationUtils->getLastAuthenticationError();
    // dernier identifiant de connexion utilisé
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('user/registration.html.twig', [
    'last_username' => $lastUsername,
    'error' => $error,
    ]
    );
    }
     */

    /**
     * @Route("/login", name="user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // erreur d'authentification (s'il y en a une)
        $error = $authenticationUtils->getLastAuthenticationError();
        // dernier identifiant de connexion utilisé
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]
        );
    }

    /**
     * @Route("/logout", name="user_logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
