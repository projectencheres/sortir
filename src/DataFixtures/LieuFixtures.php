<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LieuFixtures extends Fixture{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer des lieux fictifs associés aux villes
        for ($i = 0; $i < 20; $i++) { 
            $lieu = new Lieu();
            $lieu->setNom($faker->company) 
                ->setRue($faker->streetAddress) 
                ->setLongitude($faker->longitude) 
                ->setLatitude($faker->latitude) 
                ->setVille($faker->city) 
                ->setCodePostal($faker->postcode); 

            $manager->persist($lieu);
        }

        // Sauvegarder les lieux en base
        $manager->flush();
    }

}
