<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Validator\Constraints\Date;

class ParticipantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR'); // Utilisation de Faker avec localisation française

//        // Exemple de création de données de test pour l'entité Participant
//        $participant = new Participant();
//        $participant->setPseudo('testuser');
//        $participant->setRoles(['ROLE_USER']);
//        $participant->setPassword(password_hash('password', PASSWORD_BCRYPT));
//        $participant->setNom('Test');
//        $participant->setPrenom('User');
//        $participant->setTelephone(123456789);
//        $participant->setEmail('testuser@example.com');
//        $participant->setAdministrateur(false);
//        $participant->setActif(true);
//        $participant->setCreatedAt(new \DateTimeImmutable());
//        $manager->persist($participant);

        // Ajoutez d'autres participants ici si nécessaire
        for ($i = 1; $i <= 10; $i++) { // Création de 10 sites
            $participant = new Participant();
            $participant->setPseudo($faker->name);
            $participant->setRoles(['ROLE_USER']);
            $participant->setPassword(password_hash('password', PASSWORD_BCRYPT));
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setTelephone($faker->phoneNumber);
            $participant->setEmail($faker->email);
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setCreatedAt(new \DateTimeImmutable()); // Date de création
            $manager->persist($participant);
        }

        $manager->flush();
    }
}
