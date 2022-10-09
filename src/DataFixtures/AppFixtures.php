<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public $hasher;
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager):void
    {
        $faker = Factory::create("fr-FR");
        $users=[];

        //Création de l'admin pour les tests
        $admin = new User;
        $admin->setEmail('Naerys@test.com')
        ->setPassword($this->hasher->hashPassword($admin,'testtest'))
        ->setNickname('Naerys')
        ->setRoles(['ROLE_ADMIN'])
        ->setFirstname('Laura')
        ->setLastname('Admin')
        ->setAddress("40 rue d'Aoste")
        ->setZip('11100')
        ->setCity('Narbonne')
        ;

        $manager->persist($admin);

        //Création d'un jeu de fausses données pour les utilisateurs
        for ($i = 0; $i < 8 ; $i++){
            $user = new User;

            $user->setEmail($faker->safeEmail())
            ->setPassword($this->hasher->hashPassword($user,'testtest'))
            ->setNickname($faker->userName())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setAddress($faker->address())
            ->setZip($faker->postcode())
            ->setCity($faker->city())
            ;

            //Pour mettre sur quelques utilisateurs un complément d'adresse
            $rand = random_int(0,4);
                if($rand >= 2 ){
                    $user->setAddressComplement($faker->secondaryAddress());
                }


            $users[] = $user;
            $manager->persist($user);
            
        }

        $manager->flush();
    }
}
