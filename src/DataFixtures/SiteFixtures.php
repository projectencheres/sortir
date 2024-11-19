<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Site;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class SiteFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation de Faker avec localisation française

        for ($i = 1; $i <= 10; $i++) { // Création de 10 sites
            $site = new Site();
            $site->setNom($faker->company) // Nom du site basé sur un nom d'entreprise
                ->setCreatedAt(new \DateTimeImmutable()) // Date de création
                ->setModifiedAt($faker->boolean(50) ? new \DateTimeImmutable() : null); // Modifié ou non

            $manager->persist($site);
        }

        $manager->flush(); // Enregistre les données en base
    }
    
}
