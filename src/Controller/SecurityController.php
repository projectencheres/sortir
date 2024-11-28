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
    public function register(
        Request $request,
        InvitationLinkService $invitationService,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ): Response {
        $signed = $request->query->get('signed');
        $userData = null;

        // Vérifier si nous avons un lien signé
        if ($signed) {
            $userData = $invitationService->validateSignedData($signed);
            if (!$userData) {
                $this->addFlash('error', 'Le lien d\'invitation est invalide ou a expiré.');
                return $this->redirectToRoute('app_home');
            }
        }

        // Rechercher un utilisateur existant par email
        $existingUser = null;
        if ($userData && isset($userData['email'])) {
            $existingUser = $entityManager->getRepository(Participant::class)
                ->findOneBy(['email' => $userData['email']]);
        }

        // Création du formulaire avec l'utilisateur existant ou un nouveau
        $user = $existingUser ?? new Participant();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        // Pré-remplissage du formulaire si nous avons des données d'invitation
        if ($userData && !$form->isSubmitted()) {
            $form->setData([
                'email' => $userData['email'] ?? null,
                'pseudo' => $userData['pseudo'] ?? null,
                'nom' => $userData['nom'] ?? null,
                'prenom' => $userData['prenom'] ?? null,
                'telephone' => $userData['telephone'] ?? null
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Encodage du mot de passe
            $hashedPassword = $passwordEncoder->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            if ($existingUser) {
                // Mise à jour du mot de passe uniquement pour un utilisateur existant
                $existingUser->setPassword($hashedPassword);
                $user->setActif(true);
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
            } else {
                // Création complète du nouvel utilisateur
                $user->setPassword($hashedPassword);
                $user->setRoles(['ROLE_USER']);
                $user->setActif(true);
                $user->setEmail($form->get('email')->getData());
                $user->setPseudo($form->get('pseudo')->getData());
                $user->setNom($form->get('nom')->getData());
                $user->setPrenom($form->get('prenom')->getData());
                $user->setTelephone($form->get('telephone')->getData());

                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre compte a été créé avec succès.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
