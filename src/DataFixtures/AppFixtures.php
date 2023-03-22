<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Drone;
use App\Entity\Image;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Counter;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
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
        $users = [];

        //Création de l'admin
        $admin = new User();
        $adminDrone = new Drone();

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
        
        //Drone (config) de l'admin pour tests
        $adminDrone->setFrame('Sloop V3 - Pirat Frame')
                   ->setMotors('Lumenier JohnnyFPV Cinematic - 1750Kv')
                   ->setFc('Hobbywing F7')
                   ->setEsc('Hobbywing 60A')
                   ->setCam('Cam DJI')
                   ->setReception('Crossfire')
                   ->setLipoCells(6)
                   ->setUser($admin)      
        ;

        $manager->persist($adminDrone);        

        //Création d'un jeu de fausses données pour les utilisateurs
        for ($i = 0; $i < 8 ; $i++){
            $user = new User();

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


        //Mise en place des Catégories d'articles de base
        $categories = [];
        $categoriesName = array('Session', 'Crash', 'Build', 'Review', 'Question', 'Inclassable');

        foreach($categoriesName as $value){
            $category = new Category();
            $category->setName($value);
            $categories[] = $category;
            $manager->persist($category);
        }

        $manager->flush($category);

        //Mise en place des counters
        $counters = [];
        $countersName = array('Lipo','ESC','Frame');

        foreach($countersName as $value){
            $counter = new Counter();
            $counter->setName($value);
            $counters[] = $counter;
            $manager->persist($counter);
        }


        //Création d'un jeu de fausses données pour les articles
        $articles = [];

        for ($j = 0; $j < 20 ; $j++){

            $article = new Article();

            $randNumber = $faker->randomDigitNotNull();
            $randCategory = $categories[array_rand($categories)];
            $randAuthor = $users[array_rand($users)];

            $article->setTitle($faker->sentence())
                    ->setContent($faker->paragraphs($randNumber, true))
                    ->setCategory($randCategory)
                    ->setAuthor($randAuthor)
                    ;

           $articles[] = $article;
           $manager->persist($article);
            
        }

        //Ajout de commentaires sur les articles
        $comments = [];

        for ($k = 0; $k < 30 ; $k++){
            $comment = new Comment();
            $comment->setArticle($articles[array_rand($articles)])
                    ->setAuthor($users[array_rand($users)])
                    ->setContent($faker->sentences($randNumber, true))
                    ;

            $comments[]= $comment;
            $manager->persist($comment);
        }

        //Création d'un article à la Une
        $adminArticle = new Article();
        $adminNewsImage = new Image();
        $adminNewsImage->setSource('DWS.jpg')->setArticle($adminArticle);

        $adminArticle->setAuthor($admin)
                    ->setTitle("Évènement - Drone Winter Session : 1ère Edition")
                    ->setContent("En partenariat avec l'association EASY'CAP, SO FPV propose à ses membres une session de vol indoor: <br/><br/> - Dimanche 15 Janvier 2023 de 9h à 15h00 <br/> - Gymnase OYSTERMEYER 26 Route de Portet 31270 VILLENEUVE TOLOSANE <br/> - Droit d'entrée 4 euros (espèces ou Paypal) couvrant frais de chauffage, electricité et assurance <br/>- Tout type de drones jusqu'à 5 pouces, failsafe obligatoire (contrôle en début de session)<br/>- 20 participants maximum <br/><br/> Ouvert à tous niveaux, pas de compétition, juste du fun et de la bonne humeur. <br/> Me contacter par Facebook en MP pour inscription ou en commentaire ci-dessous.")
                    ->setCategory($categories[1])
                    ->setAdminNews(1)
                    ->addImage($adminNewsImage)
                    ;
        $manager->persist($adminArticle); 
        
        //creation d'un jeu de produits pour la boutique
        $products = [];
        for($p = 0 ; $p < 10 ; $p++){
            $product = new Product();
            $product->setName($faker->words(5, true))
                    ->setPrice($faker->randomFloat(2, 5, 30))
                    ->setDescription($faker->paragraph(2))
                    ->setImage("https://placehold.co/600x600?text=Produit")
                    ;
            $products[] = $product;
            $manager->persist($product);

        }


        $manager->flush();
    }

   
}
