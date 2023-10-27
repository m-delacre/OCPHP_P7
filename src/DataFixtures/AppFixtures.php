<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        // Phone fixtures
        for ($i = 0; $i < 30; $i++) {
            $phone = new Phone();
            $phone->setMarque($faker->randomElement(['Apple', 'Xiaomi', 'Samsung', 'OnePlus', 'Google', 'Motorola']));
            $phone->setBattery((string)$faker->numberBetween(2000, 10000) . " mAh");
            $phone->setModel(strtoupper($faker->randomLetter()) . (string)$faker->numberBetween(2, 30));
            $phone->setColors($faker->randomElements(["Bleu", "Rose", "Vert", "Or", "Pourpre", "Granite"], 3, false));
            $phone->setPrice((string)$faker->randomFloat(2, 300, 2000));

            $manager->persist($phone);
        }

        // Company fixtures
        $company = new Company();
        $company->setEmail("user@orange.fr");
        $company->setRoles(["ROLE_USER","ROLE_ORANGE"]);
        $company->setPassword($this->userPasswordHasher->hashPassword($company, "password"));
        $company->setName("ORANGE");
        $manager->persist($company);

        $company2 = new Company();
        $company2->setEmail("user@free.fr");
        $company2->setRoles(["ROLE_USER","ROLE_FREE"]);
        $company2->setPassword($this->userPasswordHasher->hashPassword($company2, "password"));
        $company2->setName("FREE");
        $manager->persist($company2);

        $manager->flush();
    }
}
