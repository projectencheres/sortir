<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\RegistrationType;
use App\Service\InvitationLinkService;
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
    public function register(Request $request, InvitationLinkService $invitationService, UserPasswordHasherInterface $passwordEncoder, EntityManagerInterface $entityManager): Response
    {

        $signed = $request->query->get('signed');
        $userData = null;

        // Si pas de signature, rediriger
        // à voir comment implétementer le liens sécurisé avec l'inscription normal
        /*         if (!$signed) {
            return $this->redirectToRoute('app_home');
        } */
        $user = new Participant();

        if ($signed) {
            $userData = $invitationService->validateSignedData($signed);
            if (!$userData) {
                $this->addFlash('error', 'Le lien d\'invitation est invalide ou a expiré.');
                return $this->redirectToRoute('app_home');
            }

            // Remplir l'objet Participant avec les données du formulaire
            $user->setPseudo($userData['pseudo'] ?? null);
            $user->setEmail($userData['email'] ?? null);
            $user->setNom($userData['nom'] ?? null);
            $user->setPrenom($userData['prenom'] ?? null);
            $user->setTelephone($userData['telephone'] ?? null);
        }

        // Création du formulaire avec les données pré-remplies
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifiez que l'email correspond à celui du lien
            if ($userData && $form->get('email')->getData() !== $userData['email']) {
                $this->addFlash('error', 'L\'invitation est invalide.');
                return $this->redirectToRoute('app_register', ['signed' => $signed]);
            }


            // Vérifier la signature
            if (!$invitationService->validateSignedData($signed)) {
                $this->addFlash('error', 'Le lien d\'invitation est invalide ou a expiré.');
                return $this->redirectToRoute('app_home');
            }


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
