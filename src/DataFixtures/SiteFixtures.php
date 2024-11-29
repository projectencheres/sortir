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
        $faker = Factory::create('fr_FR'); 

        for ($i = 1; $i <= 10; $i++) { 
            $site = new Site();
            $site->setNom($faker->company) 
                ->setCreatedAt(new \DateTimeImmutable()) // Date de création
                ->setModifiedAt($faker->boolean(50) ? new \DateTimeImmutable() : null); 

            $manager->persist($site);
        }

        $manager->flush(); 
    }
    
}
