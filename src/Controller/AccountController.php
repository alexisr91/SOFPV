<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Drone;
use App\Entity\Article;
use App\Form\DroneType;
use App\Form\ArticleType;
use App\Form\ProfileType;
use App\Form\RegisterType;
use App\Repository\VideoRepository;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    //Connexion 
    #[Route('/login', name: 'account_login')]
    public function login(AuthenticationUtils $authenticationUtils):Response
    {
        //on récupère l'erreur si il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        //on récupère le dernier identifiant de connexion entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'title' => 'Connexion', 'last_username'=>$lastUsername, 'error'=> $error
        ]);
    }

    //Déconnexion
    #[Route('/logout', name:'account_logout')]
    public function logout()
    {
        
    }

    //Inscription de l'utilisateur
    #[Route('/register', name: 'account_register')]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        //Si le formulaire est soumis réellement, et si il est valide
        if($form->isSubmitted() && $form->isValid()){

            //Hash du mot de passe avant l'envoi dans la base de données
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();
            
            $this->addFlash('success', 'Votre compte a bien été créé !');
            return $this->redirectToRoute('account_login');
        }

        return $this->render('account/register.html.twig', [
            'title' => 'Inscription',
            'form'=>$form->createView()
        ]);
    }

    //Profil personnel de l'utilisateur (modifications paramètres user, vue globale de son profil)
    #[Route('/profile', name:'account_myprofile')]
    #[IsGranted("ROLE_USER")]
    public function myProfile(VideoRepository $repo)
    {
        $user = $this->getUser();
        
        // $videos = $user->getVideos();
       
        return $this->render('account/myprofile.html.twig', [
            'title' => 'Mon compte ',
            'user' => $user,
            // 'videos'=>$videos
        ]);
    }

    //Edition des données personnelles du profil
    #[Route('/profile/edit', name:'account_edit')]
    #[IsGranted("ROLE_USER")]
    public function edit(EntityManagerInterface $manager, Request $request, SluggerInterface $slugger){

        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //ajout d'avatar par l'user
            $avatar = $form->get('avatar')->getData();
            //on récupère le nom du fichier
            if($avatar) {
                $originalName = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                //on utilise un slug pour éviter les problèmes avec les noms fournis par les users
                $sluggedName = $slugger->slug($originalName);
                //on ajoute un uniqId pour chaque upload pour éviter les problèmes de doublons + on récupère l'extension du fichier
                $newName = $sluggedName.'-'.uniqid().'.'.$avatar->guessExtension();

                //on s'assure que le deplacement dans le dossier uploads est correctement effectué
                try {
                    $avatar->move($this->getParameter('upload_avatar'), $newName);
                } catch(FileException $e) {
                    dd($e->getMessage());
                    
                }

                $user->setAvatar($newName);
            }

                //ajout d'une bannière par l'user
                $banner = $form->get('banner')->getData();
                //on récupère le nom du fichier
                if($banner) {

                    $originalName = pathinfo($banner->getClientOriginalName(), PATHINFO_FILENAME);
                    $sluggedName = $slugger->slug($originalName);
                    $newName = $sluggedName.'-'.uniqid().'.'.$banner->guessExtension();
    
                    try {
                        $banner->move($this->getParameter('upload_banner'), $newName);
                    } catch(FileException $e) {
                        dd($e->getMessage());
                    }
    
                    $user->setBanner($newName);
    
                }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour !');
            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/edit.html.twig', [
            'title' => 'Modifier le profil ',
            'user' => $user,
            'form'=>$form->createView()
        ]);
    }

    //Edition de mon drone (ma configuration favorite, visible au public) + vue sur ses autres configs (raccourcis pour en changer) 
    #[Route('/profile/edit/favorite', name:'account_drone_edit')]
    #[IsGranted("ROLE_USER")]
    public function droneEdit(EntityManagerInterface $manager, Request $request, SluggerInterface $slugger){

        $user = $this->getUser();
        //on tente de récupérer le drone associé à l'user
        $drone = $user->getDrone();
       
        $form = $this->createForm(DroneType::class, $drone);
        $form->handleRequest($request);

        if($form->isSubmitted()&& $form->isValid()){

            //si il n'y a pas de drone enregistré pour l'user
            if($drone == null){

                //création d'un drone
                $drone = new Drone();

                //on récupère les données entrées par l'user
                $frame = $form->get('frame')->getData();
                $motors = $form->get('motors')->getData();
                $fc = $form->get('fc')->getData();
                $esc = $form->get('esc')->getData();
                $cam = $form->get('cam')->getData();
                $reception = $form->get('reception')->getData();
                $lipo = $form->get('lipoCells')->getData();

                //set des données sur le nouvel objet Drone et association avec l'user actuel
                $drone->setFrame($frame)
                    ->setMotors($motors)
                    ->setFc($fc)
                    ->setEsc($esc)
                    ->setCam($cam)
                    ->setReception($reception)
                    ->setLipoCells($lipo)
                    ->setUser($user);
                
                    $manager->persist($drone);
            }

             //ajout d'image pour le drone (pas obligatoire)
             $image = $form->get('image')->getData();
             
             if($image) {
                 $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                
                 $sluggedName = $slugger->slug($originalName);
    
                 $newName = $sluggedName.'-'.uniqid().'.'.$image->guessExtension();
 
                 try {
                     $image->move($this->getParameter('upload_drone'), $newName);
                 } catch(FileException $e) {
                     dd($e->getMessage());
                     
                 }
                 $drone->setImage($newName);
            }
 
            $manager->persist($drone);
            //envoi en bdd du nouveau drone / de la modification du drone
            $manager->flush();
            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/droneEdit.html.twig', [
            'title' => 'Ajouter ou modifier mon drone ',
            'user'=>$user,
            'drone'=>$drone,
            'form'=>$form->createView()
        ]);

        
    }

    //Visualisation des articles de l'utilisateur connecté sur sa page profil
    #[Route('/profile/articles', name:"account_articles")]
    #[IsGranted("ROLE_USER")]
    public function myArticles(ArticleRepository $articleRepository){

        $user = $this->getUser();
        //Récupération des articles de l'user connecté par date de publication la plus récente
        $articles = $articleRepository->findBy(['author'=> $user], ['createdAt'=>'DESC']);
    

        return $this->render('account/article/index.html.twig', [
            'title'=> 'Mes articles ',
            'user'=>$user,
            'articles'=>$articles
            
        ]);
    }

    //Edition d'un article
    #[Route('/profile/articles/edit/{id}', name:"account_article_edit")]
    #[IsGranted("ROLE_USER")]
    public function editArticle(Article $article, EntityManagerInterface $manager, Request $request){
        
        $user = $this->getUser();
        
        if($user == $article->getAuthor()){
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $manager->persist($article);
                $manager->flush();

                $this->addFlash("success", "Votre article a bien été modifié !");

                return $this->redirectToRoute('account_articles');
            }


        } else {
            return $this->redirectToRoute('home');
        }

        //TODO : modifier pour la fonctionnalité d'images 
        return $this->render('account/article/edit.html.twig',[
            'title'=> 'Edition de l\'article',
            'article'=>$article,
            'form'=>$form->createView()
        ]);

    }

        //Suppression d'un article
        #[Route('/profile/articles/delete/{id}', name:"account_article_delete")]
        #[Security("is_granted('ROLE_USER') and user == article.getAuthor()", message:"Vous n'avez pas le droit d'accéder à cette page.")]
        public function deleteArticle(Article $article, EntityManagerInterface $manager){
            
            $user = $this->getUser();
            
            if($user == $article->getAuthor()){
    
                    $manager->remove($article);
                    $manager->flush();
    
                    $this->addFlash("success", "Votre article a bien été supprimé !");
    
                    return $this->redirectToRoute('account_articles');
                } else {

                return $this->redirectToRoute('home');
            }
    
        }

    //Profil utilisateur public (vidéos de l'user, badge helper, drone favori et sa configuration)
    // #[Route('/profile/{nickname}', name:'account_profile')]
    // public function profile()
    // {

    // }

}
