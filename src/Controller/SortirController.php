<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Form\SortieCreateType;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

        #[Route('/sortie/create', name: 'app_sortie_create')]
        public function create(
            Request $request,
            EntityManagerInterface $entityManager,
            LieuRepository $lieuRepository
        ): Response {

            $sortie = new Sortie();
            $form = $this->createForm(SortieCreateType::class, $sortie);
            $form->handleRequest($request);

            $lieuForm = $this->createForm(LieuType::class, new Lieu());
            $lieuForm->handleRequest($request);
            $participant = $this->participantRepository->find($this->getUser()->getId());

            if ($form->isSubmitted() && $form->isValid()) {
                // Vérifier si l'utilisateur a sélectionné un lieu existant
                $lieuId = $form->get('lieu')->getData();
                if ($lieuId) {
                    $lieu = $lieuRepository->find($lieuId);
                    $sortie->setLieu($lieu);
                } elseif ($lieuForm->isSubmitted() && $lieuForm->isValid()) {
                    // Si aucun lieu n'est sélectionné, prendre le nouveau lieu du formulaire
                    $nouveauLieu = $lieuForm->getData();
                    $entityManager->persist($nouveauLieu);
                    $sortie->setLieu($nouveauLieu);
                } else {
                    $this->addFlash('error', 'Veuillez sélectionner ou créer un lieu.');
                    return $this->redirectToRoute('app_sortie_create');
                }
                
                $sortie->setEtat('Créée');
                $sortie->addParticipant($participant);
                $sortie->setOrganisateur($this->getUser()); // Associer l'organisateur
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'La sortie a été créée avec succès.');
                return $this->redirectToRoute('app_all_sorties');
            }

            return $this->render('sortir/create.html.twig', [
                'form' => $form->createView(),
                'lieuForm' => $lieuForm->createView(),
            ]);
    }

    #[Route('/sorties/list', name: 'app_all_sorties')]
    public function index(): Response
    {   
        $sorties = $this->sortieRepository->findAll();
        return $this->render('sortir/all_sorties.html.twig', [
            'sorties' => $sorties,
        ]);
    }

    // route pour s'inscrire a une sortie
    #[Route('/sortie/subscribe/{id}', name: 'app_sortir_subscribe')]
    public function subscribe(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('app_all_sorties');
        }

        $participant = $this->participantRepository->find($this->getUser()->getId());
        //dd($participant);
        if (!$participant) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if ($sortie->getParticipants()->contains($participant)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if ($sortie->getDateLimiteInscription() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'La date limite d\'inscription à cette sortie est dépassée.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()) {
            $this->addFlash('error', 'Le nombre maximum de participants a été atteint.');
            return $this->redirectToRoute('app_all_sorties');
        }

        $sortie->addParticipant($participant);
        // $sortie->setOrganisateur($this->getUser());

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la sortie avec succès.');
        return $this->redirectToRoute('app_all_sorties');
    }

    #[Route('/sortie/unsubscribe/{id}', name: 'app_sortir_unsubscribe')]
    public function unsubscribe(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('app_all_sorties');
        }

        $participant = $this->participantRepository->find($this->getUser()->getId());

        if (!$participant) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if (!$sortie->getParticipants()->contains($participant)) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if ($sortie->getDateLimiteInscription() <= new \DateTimeImmutable()) {
            $this->addFlash('error', 'La date limite de désinscription à cette sortie est dépassée.');
            return $this->redirectToRoute('app_all_sorties');
        }

        // Retirer le participant de la sortie
        $sortie->removeParticipant($participant);

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous êtes désinscrit de la sortie avec succès.');
        return $this->redirectToRoute('app_all_sorties');
    }

    #[Route('/sortie/cancel/{id}', name: 'app_sortir_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);

        if (!$sortie) {
            return $this->json(['error' => 'Sortie introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($sortie->getDateHeureDebut() <= new \DateTimeImmutable()) {
            return $this->json(['error' => 'Vous ne pouvez pas annuler une sortie qui a déjà commencé.'], Response::HTTP_BAD_REQUEST);
        }

        if ($this->getUser() !== $sortie->getOrganisateur()) {
            return $this->json(['error' => 'Seul l\'organisateur peut annuler cette sortie.'], Response::HTTP_FORBIDDEN);
        }

        $form = $this->createFormBuilder()
            ->add('motif', TextareaType::class, [
                'label' => 'Motif d\'annulation',
                'required' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $sortie->setEtat('Annulée');
            $sortie->setMotifAnnulation($data['motif']);

            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            return $this->json(['success' => 'La sortie a été annulée avec succès.']);
        }

        return $this->render('sortir/_cancel_form.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }

    //route pour supprimer une sortie
    #[Route('/sortie/delete/{id}', name: 'app_sortir_delete')]
    public function delete(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('app_all_sorties');
        }

        if ($this->getUser() !== $sortie->getOrganisateur()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette sortie.');
            return $this->redirectToRoute('app_all_sorties');
        }

        $this->entityManager->remove($sortie);
        $this->entityManager->flush();

        $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        return $this->redirectToRoute('app_all_sorties');
    }

    //route pour afficher les détails d'une sortie
    #[Route('/sortie/{id}', name: 'app_sortir_show')]
    public function show(int $id): Response
    {
        $sortie = $this->sortieRepository->find($id);
        //dd($sortie);
        if (!$sortie) {
            $this->addFlash('error', 'La sortie demandée n\'existe pas.');
            return $this->redirectToRoute('app_all_sorties');
        }

        return $this->render('sortir/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

}
