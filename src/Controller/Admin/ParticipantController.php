<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Form\CsvUploadType;
use App\Repository\ParticipantRepository;
use App\Service\InvitationLinkService;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('admin/participants', name: 'app_admin_participants_')]
class ParticipantController extends AbstractController
{
    private MailerInterface $mailer;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository,
        private SortieRepository $sortieRepository,
        MailerInterface $mailer
    ) {
        $this->mailer = $mailer;
    }

    #[Route('/create-massiv', name: 'create_massiv')]
    public function index(Request $request): Response
    {
        // Créer le formulaire
        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        $csvData = [];
        $headers = [];

        // Si un fichier a été uploadé
        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csvFile')->getData();

            if ($csvFile) {
                $fileContent = file_get_contents($csvFile->getPathname());
                $rows = array_map('str_getcsv', explode("\n", $fileContent));

                // Récupérer les en-têtes (première ligne)
                $headers = array_shift($rows);

                // Supprimer les lignes vides
                $csvData = array_filter($rows, function ($row) use ($headers) {
                    return count($row) === count($headers);
                });
            }
        }

        return $this->render('admin/participant/csvimport.html.twig', [
            'form' => $form->createView(),
            'headers' => $headers,
            'csvData' => $csvData,
        ]);
    }

    #[Route('/send-invitations', name: 'send_invitations', methods: ['POST'])]
    public function sendInvitations(
        Request $request,
        InvitationLinkService $invitationService,
        UserPasswordHasherInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $logger->info('Début du traitement des invitations');

        $data = $request->request->all('data');
        $logger->debug('Données reçues :', ['data' => $data]);

        $sentCount = 0;


        if (!is_array($data)) {
            $this->addFlash('error', 'Aucune donnée valide reçue');
            return $this->redirectToRoute('app_admin_participant');
        }

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            $userData = [
                'pseudo' => $row[1] ?? '',
                'email' => $row[7] ?? null,
                'nom' => $row[4] ?? '',
                'prenom' => $row[5] ?? '',
                'telephone' => $row[6] ?? ''
            ];

            $emailTo = $userData['email'];

            if (!$emailTo) {
                continue;
            }
            $logger->info('Tentative d\'envoi à : ' . $emailTo);

            // try catch to register the user in database
            try {

                $user = new Participant();
                $user->setPseudo($userData['pseudo'] ?? null);
                $user->setEmail($userData['email'] ?? null);
                $user->setNom($userData['nom'] ?? null);
                $user->setPrenom($userData['prenom'] ?? null);
                $user->setTelephone($userData['telephone'] ?? null);
                $user->setRoles(['ROLE_USER']);
                $user->setActif(false);
                $user->setAdministrateur(false);
                $user->setPassword($passwordEncoder->hashPassword($user, 'password'));
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', "Erreur lors de l'enregistrement de {$emailTo} : {$e->getMessage()}");
                $logger->error('Erreur lors de l\'enregistrement de l\'utilisateur : ' . $e->getMessage(), [
                    'email' => $emailTo,
                ]);
            }



            try {
                $signedData  = $invitationService->generateSignedEmail($userData);

                $email = (new Email())
                    ->from('sortir.local@testnet.com')
                    ->to($emailTo)
                    ->subject('Invitation à rejoindre notre plateforme')
                    ->html($this->renderView('emails/invitations.html.twig', [
                        'nom' => $userData['nom'],
                        'prenom' => $userData['prenom'],
                        'inscriptionLink' => $this->generateUrl('app_register', [
                            'signed' => $signedData
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]));

                $logger->debug('Email préparé pour : ' . $emailTo);

                $this->mailer->send($email);
                $logger->info('Email envoyé avec succès à : ' . $emailTo);
                $sentCount++;
            } catch (\Exception $e) {
                $this->addFlash('error', "Erreur lors de l'envoi à {$emailTo}: {$e->getMessage()}");
                $logger->error('Erreur lors de l\'envoi : ' . $e->getMessage(), [
                    'email' => $emailTo,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->addFlash('success', "{$sentCount} invitations ont été envoyées avec succès.");
        return $this->redirectToRoute('app_admin_participants_create_massiv');
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function showParticipants(): Response
    {

        $participants = $this->participantRepository->findAll();
        return $this->render('admin/participant/participant_list.html.twig', [
            "participants" => $participants,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function participantDetail(int $id): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }
        return $this->render('participant/profile.html.twig', [
            "participant" => $participant,
        ]);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deactivateParticipant(int $id): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Cannot deactivate " . $participant->getPseudo());
        }

        if ($participant->isActif() === true) {
            $participant->setActif(false);
            $participant->setModifiedAt(new \DateTimeImmutable());
            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            $this->addFlash("success", $participant->getPseudo() . " deactivated");
        } else {
            $this->addFlash("danger", "Unable to deactivate ". $participant->getPseudo());
        }
        return $this->redirectToRoute('app_admin_participants_list');
    }

    #[Route('/{id}/reactivate', name: 'reactivate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function reactivateParticipant(int $id): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Cannot reactivate participant');
        }

        if ($participant->isActif() === false || $participant->isActif() === null) {
            $participant->setActif(true);
            $participant->setModifiedAt(new \DateTimeImmutable());
            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            $this->addFlash("success", $participant->getPseudo() . " reactivated");
        } else {
            $this->addFlash("danger", "Unable to reactivate ". $participant->getPseudo());
        }
        return $this->redirectToRoute('app_admin_participants_list');
    }


    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteParticipant(int $id): Response {

        $currentUser = $this->getUser();
        $participant = $this->participantRepository->find($id);
        $organiserSorties = $this->sortieRepository->findUpcomingSortiesByOrganizer($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Not authorised to delete participant");
        }

        if($currentUser->getUserIdentifier() === $participant->getUserIdentifier()) {
            throw $this->createAccessDeniedException("Cannot delete your account");
        }

        // don't delete this participant if s·he is the organiser of any upcoming events
        if (!empty($organiserSorties)) {
            throw $this->createAccessDeniedException("Cannot delete participant because they have organised upcoming events.");
        }

        $this->entityManager->remove($participant);
        $this->entityManager->flush();
        $this->addFlash("success", $participant->getPseudo() . " deleted");

        return $this->redirectToRoute('app_admin_participants_list');
    }
}
