<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer des villes fictives
        $villes = [];
        for ($i = 0; $i < 5; $i++) { // Générer 5 villes
            $ville = new Ville();
            $ville->setNom($faker->city)
                ->setCodePostal($faker->numberBetween(10000, 99999));
            $manager->persist($ville);
            $villes[] = $ville; // Sauvegarde pour les utiliser dans les lieux
        }

        // Créer des lieux fictifs associés aux villes
        for ($j = 0; $j < 20; $j++) { // Générer 20 lieux
            $lieu = new Lieu();
            $lieu->setNom($faker->streetName)
                ->setRue($faker->streetAddress)
                ->setLatitude($faker->latitude)
                ->setLongitude($faker->longitude)
                ->setVille($faker->randomElement($villes)); // Associer à une ville au hasard
            $manager->persist($lieu);
        }

        // Sauvegarder tout en base
        $manager->flush();
    }
    
}
