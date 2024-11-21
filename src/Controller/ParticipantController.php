<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participant', name: 'participant_')]
class ParticipantController extends AbstractController
{

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function detail(): Response
    {
        $participant = $this->getUser();

        if (!$participant instanceof Participant) {
            throw $this->createAccessDeniedException('You must be logged in to view your profile');
        }
        return $this->render('participant/profile.html.twig', [
            'participant' => $participant,
        ]);
    }
    #[Route('/{id}/edit', name: 'edit')]
    public function edit(
        Participant $participant,
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader
    ): Response
    {

        /*
         * fixes to be done: telephone needs to be rendered with the leading 0,
         * photo needs to be rendered on the profile page and probably saved in the db as well
         */

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        // ensure that the profile is being modified by the current user
        if ($this->getUser() !== $participant) {
            return $this->redirectToRoute('app_home');
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if($participantForm->isSubmitted() && $participantForm->isValid()) {

            $photo = $participantForm->get('profileImage')->getData();
            if(($participantForm->has('deleteImage') && $participantForm['deleteImage']->getData()) || $photo) {
                $fileUploader->delete($participant->getFilename(), $this->getParameter('photos_directory'));
                if($photo) {
                    $participant->setFilename($fileUploader->upload($photo));
                } else {
                    $participant->setFilename(null);
                }
            }

            $participant->setModifiedAt(new \DateTimeImmutable());
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations de votre compte ont bien ete modifiees.');
            return $this->redirectToRoute('participant_profile', ['id' => $participant->getId()]);
        }

        return $this->render('participant/edit.html.twig', [
            'participantForm' => $participantForm->createView(),
        ]);
    }
}
