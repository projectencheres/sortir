<?php

namespace App\Controller;

use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SortirController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository,
        private SortieRepository $sortieRepository,
        private LieuRepository $lieuRepository,
    )
    {
    }

    // route afficher les sorties
    #[Route('/sortir/show', name: 'app_sortir')]
    public function index(): Response
    {   
        $sorties = $this->sortieRepository->findAll();
        return $this->render('sortir/show_sorties.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    // route pour s'inscrire a une sortie
    #[Route('/sortir/subscribe/{id}', name: 'app_sortir_subscribe')]
    public function subscribe(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);
        $participant = $this->participantRepository->find($this->getUser()->getId());
        $sortie->addParticipant($participant);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_sortir');
    }
}
