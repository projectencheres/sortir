<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile', name: 'participant_')]
class ParticipantController extends AbstractController
{
    #[Route('/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        UserPasswordHasherInterface $passwordHasher,
    ): Response
    {
        $participant = $this->getUser();
        // Ensure participant is logged in
        if (!$participant instanceof Participant) {
            throw $this->createAccessDeniedException();
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $participant->setModifiedAt(new \DateTimeImmutable());
            // handle password
            $newPassword = $participantForm->get('plainPassword')->getData();
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($participant, $newPassword);
                $participant->setPassword($hashedPassword);
            }

            // handle photo upload
            $photo  = $participantForm->get('photo')->getData();
            if ($photo) {
                $participant->setFilename($fileUploader->upload($photo));
            } else {
                $participant->setFilename(null);
            }
            $entityManager->persist($participant);
            $entityManager->flush();
            $this->addFlash("success", "Profile successfully updated");
            return $this->redirectToRoute('app_home', );
        }
        return $this->render(
            'participant/profile.html.twig', [
                'participantForm' => $participantForm,
            ]
        );
    }
}
