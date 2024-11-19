<?php

namespace App\Controller\Admin;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef\Sort;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route( path: '/admin')]
class SortirController extends AbstractController
{   
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository,
        private SortieRepository $sortieRepository,
    )
    {
    }
    // route pour créer une sortie
    #[Route('/sortir/create', name: 'app_sortir_create')]
    public function create(Request $request): Response
    {   
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setCreatedAt(new \DateTimeImmutable());
            $sortie->setEtat('Créée');
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            $this->addFlash('success', 'Sortie créée avec succès');
            
            return $this->redirectToRoute('app_sortir_index');
        }
        return $this->render('admin/sortie/new_sortie.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
