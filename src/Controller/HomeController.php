<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(SortieRepository $sortieRepository): Response
    {
        $sortiesPassées = $sortieRepository->findPastSorties(); 
        return $this->render('home/index.html.twig', [
            'sorties_passées' => $sortiesPassées,
        ]);
    }
}
