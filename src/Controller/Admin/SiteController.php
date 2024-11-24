<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin', name: 'app_admin_')]
class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private SiteRepository $siteRepository)
    {
    }

    #[Route('/site/add', name: 'add_site')]
    public function index(Request $request): Response
    {
        //formulaire d'ajout de site
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($site);
            $this->entityManager->flush();

            $this->addFlash('success', 'Site ajouté avec succès.');
            return $this->redirectToRoute('app_admin_all_site');
        }
        
        return $this->render('admin/site/site_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //route pour afficher la liste des sites
    #[Route('/site', name: 'all_site')]
    public function list(): Response
    {
        $sites = $this->siteRepository->findAll();
        return $this->render('admin/site/site_list.html.twig', [
            'sites' => $sites,
        ]);
    }
}
