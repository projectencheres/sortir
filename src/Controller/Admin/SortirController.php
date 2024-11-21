<?php

namespace App\Controller\Admin;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\LieuRepository;
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
        private LieuRepository $lieuRepository,
    )
    {
    }
    // route pour créer une sortie
    #[Route('/sortir/create', name: 'admin_sortir_create')]
    public function create(Request $request): Response
    {   
        $lieux = $this->lieuRepository->findAll();
        //dd($lieux);
        $dataLieux = [];
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie, [
            'lieux' => $lieux,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setCreatedAt(new \DateTimeImmutable());
            $nouveauLieuData = $form->get('nouveauLieu')->getData();
            $utiliserLieuExistant = $form->get('utiliserLieuExistant')->getData();
            
            if(!$utiliserLieuExistant && $nouveauLieuData){
                $lieu = new Lieu();
                $lieu->setNom($nouveauLieuData->getNom());
                $lieu->setRue($nouveauLieuData->getRue());
                $lieu->setLatitude($nouveauLieuData->getLatitude());
                $lieu->setLongitude($nouveauLieuData->getLongitude());
                $lieu->setVille($nouveauLieuData->getVille());
                $lieu->setCodePostal($nouveauLieuData->getCodePostal());
                
                $this->entityManager->persist($lieu);
                $sortie->setLieu($lieu);
            }

            if($utiliserLieuExistant){
                $lieu = $form->get('lieu')->getData();
                $sortie->setLieu($lieu);
            }

            $sortie->setEtat('Créée');
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès');
            
            // return $this->redirectToRoute('app_sortir_index');
        }
        foreach($lieux as $lieu){
            $dataLieux[$lieu->getId()] = [
                'rue' => $lieu->getRue(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
                'ville' => $lieu->getVille(),
                'codePostal' => $lieu->getCodePostal(),
            ];
        }
        return $this->render('admin/sortie/new_sortie.html.twig', [
            'form' => $form->createView(),
            'lieux' => $dataLieux
        ]);
    }

    // route afficher les sorties
    #[Route('/sortir/show', name: 'admin_sortir_index')]
    public function index(): Response
    {
        $sorties = $this->sortieRepository->findAll();
        //  dd($sorties);
        return $this->render('admin/sortie/showSortie.html.twig', [
            'sorties' => $sorties
        ]);
    }
}
