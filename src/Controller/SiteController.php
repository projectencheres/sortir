<?php

namespace App\Controller;

use Mpdf\Tag\P;
use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SiteController extends AbstractController
{
    public function __construct( 
        private SiteRepository $siteRepository,
        private EntityManagerInterface $entityManager,
        private ParticipantRepository $participantRepository)
    {
    }
    #[Route('/site/create', name: 'app_create_site', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {   //formulaire de création de site
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);
        $participant = $this->participantRepository->find($this->getUser()->getId());


        if ($form->isSubmitted() && $form->isValid()) {
            $site->addParticipant($participant);
            $this->entityManager->persist($site);
            $this->entityManager->flush();

            $this->addFlash('success', 'Le site a bien été créé');
            return $this->redirectToRoute('app_all_site');
        }
        
        return $this->render('site/create_site.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    //affichage de la liste des sites
    #[Route('/site/list', name: 'app_all_site')]
    public function list(): Response
    {
        $sites = $this->siteRepository->findAll();
        return $this->render('site/All_site.html.twig', [
            'sites' => $sites,
        ]);
    }

    // route pour suppression de site
    #[Route('/site/delete/{id}', name: 'app_delete_site', requirements: ['id' => '\d+'])]
    public function delete(Site $site): Response
    {
        $this->entityManager->remove($site);
        $this->entityManager->flush();
        $this->addFlash('success', 'Le site a bien été supprimé');
        return $this->redirectToRoute('app_all_site');
    }
}
