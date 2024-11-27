<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\RememberMe\RememberMeHandlerInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        RememberMeHandlerInterface $rememberMeHandler
    ): Response {
        if ($this->getUser()) {
            // Gérer le Remember Me si l'utilisateur est connecté
            if ($this->getUser()) {
                $rememberMeHandler->createRememberMeCookie($this->getUser());
            }
            return $this->redirectToRoute('app_home');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        // Nettoyage supplémentaire si nécessaire
        $session = $request->getSession();
        $session->clear();            // Vide la session
        $session->invalidate();       // Invalide la session

        $response = new Response();
        $response->headers->clearCookie('REMEMBERME');
        $response->headers->clearCookie('PHPSESSID');

        // Redirection vers la page de login
        return $this->redirectToRoute('app_login');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Enregistrer l'utilisateur
            $user->setRoles(['ROLE_USER']);
            $user->setActif(true);
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger ou afficher un message de succès
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
