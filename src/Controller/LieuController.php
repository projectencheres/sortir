<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Admin\VilleController;
use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'app_lieu_')]
class LieuController extends AbstractController
{

    private LieuRepository $lieuRepository;
    private VilleRepository $villeRepository;

    public function __construct(LieuRepository $lieuRepository, VilleRepository $villeRepository){
        $this->lieuRepository = $lieuRepository;
        $this->villeRepository = $villeRepository;
    }

    #[Route('/list', name: 'list')]
    public function show(): Response
    {
        $lieux = $this->lieuRepository->findAll();
        return $this->render('lieu/list.html.twig', [
            'lieux' => $lieux,
        ]);
    }

    #[Route('/create', name: 'create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();
            $this->addFlash("success", "Nouveau lieu ajouté!!");
            return $this->redirectToRoute('app_lieu_list');
        }

        return $this->render('lieu/create.html.twig', [
            'lieuForm' => $lieuForm->createView(),
        ]);
    }
}
