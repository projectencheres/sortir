<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Exemple de création de données de test pour l'entité Participant
        $participant = new Participant();
        $participant->setPseudo('testuser');
        $participant->setRoles(['ROLE_USER']);
        $participant->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $participant->setNom('Test');
        $participant->setPrenom('User');
        $participant->setTelephone(123456789);
        $participant->setEmail('testuser@example.com');
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $manager->persist($participant);

        // Ajoutez d'autres participants ici si nécessaire

        $manager->flush();
    }
}
