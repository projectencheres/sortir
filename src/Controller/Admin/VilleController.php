<?php

namespace App\Controller\Admin;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VilleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VilleRepository $villeRepository,
    )
    {
    }

    #[Route('/admin/villes', name: 'app_admin_ville_index')]
    public function index(Request $request, VilleRepository $villeRepository, EntityManagerInterface $em): Response
    {
        // Récupération des villes
        $search = $request->query->get('search', '');
        $villes = $villeRepository->findBySearch($search);

        // Gestion du formulaire d'ajout de ville
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();

            $this->addFlash('success', 'Ville ajoutée avec succès.');
            return $this->redirectToRoute('app_admin_ville_index');
        }

        return $this->render('admin/ville/All_city.html.twig', [
            'villes' => $villes,
            'form' => $form->createView(),
        ]);
    }
    // route pour afficher les villes
    #[Route('/admin/ville/show', name: 'app_admin_ville_show')]
    public function show(): Response
    {
        $villes = $this->villeRepository->findAll();
        return $this->render('admin/ville/All_city.html.twig', [
            'villes' => $villes,
        ]);
    }
}
