<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Assuming there are Lieu, Site, and Participant fixtures already loaded
        $lieux = $manager->getRepository(Lieu::class)->findAll();
        $sites = $manager->getRepository(Site::class)->findAll();
        $participants = $manager->getRepository(Participant::class)->findAll();

        if (empty($lieux)) {
            throw new \Exception('Aucun lieu trouvé en base. Assurez-vous que les fixtures VilleFixtures ont été exécutées.');
        }

        if (empty($sites)) {
            throw new \Exception('Aucun site trouvé en base. Assurez-vous que les fixtures SiteFixtures ont été exécutées.');
        }

        for ($i = 0; $i < 20; $i++) {
            $sortie = new Sortie();

            // Setting random but coherent dates
            $dateCreation = $faker->dateTimeBetween('-2 months', 'now');
            $dateDebut = (clone $dateCreation)->modify('+' . mt_rand(1, 30) . ' days');
            $dateLimiteInscription = (clone $dateDebut)->modify('-' . mt_rand(1, 10) . ' days');

            // Populate fields
            $sortie->setNom($faker->sentence(3))
                ->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateDebut))
                ->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimiteInscription))
                ->setDuree($faker->numberBetween(30, 300)) // Duration in minutes
                ->setNbInscriptionsMax($faker->numberBetween(10, 100))
                ->setInfosSortie($faker->paragraph())
                ->setEtat($faker->randomElement(['Créée', 'Ouverte', 'Terminée', 'Annulée']))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($dateCreation))
                ->setModifiedAt(null)
                ->setArchives(false);

            // Assign random relationships
            $sortie->setLieu($faker->randomElement($lieux));
            $sortie->setSite($faker->randomElement($sites));
            $sortie->setOrganisateur($faker->randomElement($participants));

            // Randomly assign participants
            $randomParticipants = $faker->randomElements($participants, mt_rand(2, 10));
            foreach ($randomParticipants as $participant) {
                $sortie->addParticipant($participant);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
    public function getDependencies()
    {
        // S'assurer que les fixtures VilleFixtures sont chargées avant celles-ci
        return [
            LieuFixtures::class,
            SiteFixtures::class,
        ];
    }
}
