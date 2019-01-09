<?php

// src/Controller/UserController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/registration", name="user_registration")
     */
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
