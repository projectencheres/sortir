<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/participants', name: 'app_admin_participants_')]
class ParticipantController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository
    ){}

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function showParticipants(): Response {

        $participants = $this->participantRepository->findAll();
        return $this->render('admin/participant/participant_list.html.twig', [
            "participants" => $participants,
        ]);
    }

    #[Route('/{id}', name: 'detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function participantDetail(int $id): Response {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }
        return $this->render('admin/participant/participant_detail.html.twig', [
            "participant" => $participant,
        ]);
    }

    #[Route('/{id}/deactivate', name: 'deactivate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function deactivateParticipant(int $id): Response {
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
            $this->addFlash("danger", $participant->getPseudo() . " not active");
        }
        return $this->redirectToRoute('app_admin_participants_list');
    }

    #[Route('/{id}/reactivate', name: 'reactivate', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function reactivateParticipant(int $id): Response {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Cannot reactivate participant');
        }

        if ($participant->isActif() === false) {
            $participant->setActif(true);
            $participant->setModifiedAt(new \DateTimeImmutable());
            $this->entityManager->persist($participant);
            $this->entityManager->flush();
            $this->addFlash("success", $participant->getPseudo() ." reactivated");
        } else {
            $this->addFlash("danger", $participant->getPseudo() . " already activated");
        }
        return $this->redirectToRoute('app_admin_participants_list');
    }


}
