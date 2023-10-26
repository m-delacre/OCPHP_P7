<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Phone fixtures
        for ($i = 0; $i < 30; $i++) {
            $phone = new Phone();
            $phone->setMarque($faker->randomElement(['Apple', 'Xiaomi', 'Samsung', 'OnePlus', 'Google', 'Motorola']));
            $phone->setBattery((string)$faker->numberBetween(2000,10000) . " mAh");
            $phone->setModel(strtoupper($faker->randomLetter()) . (string)$faker->numberBetween(2,30));
            $phone->setColors($faker->randomElements(["Bleu", "Rose", "Vert", "Or", "Pourpre", "Granite"], 3, false));
            $phone->setPrice((string)$faker->randomFloat(2, 300, 2000));

            $manager->persist($phone);
        }

        $manager->flush();
    }
}
