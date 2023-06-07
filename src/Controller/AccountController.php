<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Drone;
use App\Entity\Image;
use App\Entity\Article;
use App\Entity\Counter;
use App\Form\DroneType;
use App\Form\ArticleType;
use App\Form\ProfileType;
use App\Form\RegisterType;
use App\Services\Pagination;
use App\Entity\PasswordUpdate;
use App\Form\AdminArticleType;
use App\Form\PasswordUpdateType;
use App\Repository\ImageRepository;
use App\Repository\OrderRepository;
use App\Repository\ArticleRepository;
use App\Repository\SessionRepository;
use Symfony\Component\Form\FormError;
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
    public function myProfile(ArticleRepository $articleRepo, SessionRepository $sessionRepository, OrderRepository $orderRepository)
    {
        $user = $this->getUser();
  
        //nombre d'articles de l'user
        $articleCount = $articleRepo->countMyArticles($user);

        //nombre de sessions actives de l'user
        $sessionCount = $sessionRepository->countMySessions($user);

        //3 dernières questions de l'user pour accès rapides aux réponses 
        $myQuestions = $articleRepo->findMylastQuestions($user);

        //status de la dernière commande en cours (pas annulée) 
        $lastOrderStatus = $orderRepository->findLastOrder($user);


   
        return $this->render('account/myprofile.html.twig', [
            'title' => 'Mon compte ',
            'user' => $user,
            'articleCount'=>$articleCount,
            'sessionCount'=>$sessionCount,
            'questions'=>$myQuestions,
            'lastOrderStatus'=>$lastOrderStatus
        ]);
    }

    //Compteur de lipo, esc et frame mis à jour sur l'accueil
    #[Route('/profile/counter/{name}', name:'account_add_to_counter')]
    #[IsGranted("ROLE_USER")]
    public function addToCounter(Counter $counter, EntityManagerInterface $manager){

        $user = $this->getUser();
        $count = $counter->getCount();
        $counter->setCount($count+1)
                ->addUser($user);

        $manager->persist($counter);
        $manager->flush();
        
        return $this->redirectToRoute('account_myprofile');
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

    //Modification du mot de passe de l'utilisateur
    #[Route('/profile/edit/password-update', name:'account_pwd_edit')]
    #[isGranted("ROLE_USER")]
    public function passwordEdit(Request $request, EntityManagerInterface $manager,  UserPasswordHasherInterface $hasher){

        $user = $this->getUser();
        $passwordUpdate = new PasswordUpdate();
        
        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //le mot de passe actuel n'est pas bon
            if(!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())){

                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez entré n'est pas votre mot de passe actuel."));
            } else {
                //récupération du nouveau mdp
                $newPassword = $passwordUpdate->getNewPassword();

                //hash du nouveau mdp
                $hash = $hasher->hashPassword($user, $newPassword);

                //on set le nouveau mdp
                $user->setPassword($hash);

                //ok donc on envoie à la bdd
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', "Votre nouveau mot de passe a bien été enregistré.");
                return $this->redirectToRoute('account_edit');

            }

        }

        return $this->render("account/passwordUpdate.html.twig", ["title"=>" Modification de votre mot de passe", 'form'=>$form->createView()]);

    }


    //Edition de mon drone (ma configuration favorite, visible au public)
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
            $this->addFlash('success', 'Votre drone a été mis à jour !');
            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/droneEdit.html.twig', [
            'title' => 'Ajouter ou modifier mon drone ',
            'user'=>$user,
            'drone'=>$drone,
            'form'=>$form->createView()
        ]);

        
    }

    //GESTION DES ARTICLES PROPRES A L'UTILISATEUR

    //Visualisation des articles de l'utilisateur connecté sur sa page profil
    #[Route('/profile/articles/{page<\d+>?1}', name:"account_articles")]
    #[IsGranted("ROLE_USER")]
    public function myArticles(Pagination $paginationService, $page){

        $user = $this->getUser();
        //Récupération des articles de l'user connecté par date de publication la plus récente
        // $articles = $articleRepository->findBy(['author'=> $user], ['createdAt'=>'DESC']);
        $paginationService->setEntityClass(Article::class)
                         ->setPage($page)
                         ->setLimit(5)
                         ->setOrder('DESC')
                         ->setProperty('author')
                         ->setValue($user)
                        ;
    

        return $this->render('account/article/index.html.twig', [
            'title'=> 'Mes articles ',
            'user'=>$user,
            'pagination'=>$paginationService
            
        ]);
    }

    //Edition d'un article
    #[Route('/profile/articles/edit/{id}', name:"account_article_edit")]
    #[IsGranted("ROLE_USER")]
    public function editArticle(Article $article, ImageRepository $imgRepo, EntityManagerInterface $manager, Request $request, SluggerInterface $slugger){
        
        $user = $this->getUser();
        
        if($user == $article->getAuthor()){
            
        //vérification de l'accès de l'user connecté
        $adminAccess = $this->isGranted('ROLE_ADMIN');
        
        //si l'user est un admin: on lui présente le formulaire avec l'option permettant de mettre son article "à la une" de la page d'accueil
        if($adminAccess){ 
            $form = $this->createForm(AdminArticleType::class, $article);
        } else {
            //sinon l'user aura le formulaire classique, son article apparaîtra avec les autres dans la sections blog (actualités)
            $form = $this->createForm(ArticleType::class, $article); 
        }

            $form->handleRequest($request);

           //Récupération du champ source pour la vidéo
           $videoSource = $form->get('video')->get('source')->getData();

           //Récupération du lien Youtube ou Vimeo 
            $videoLink = $form->get('video')->get('link')->getData();
  
          //Si les 2 inputs concernant l'ajout d'une vidéo sont remplis, on renvoie une erreur 
          if(isset($videoLink) && isset($videoSource)){
              $form->get('video')->get('link')->addError(new FormError('Vous ne pouvez pas ajouter plusieurs vidéos à votre article : choisissez le téléversement OU l\'ajout de lien.'));
          }
  
          //limite d'images imposée par article
          $imgLimit = 10;

          //images envoyées lors du formulaire d'edition
          $images = $form->get('images')->getData();

          //images déjà présentes sur l'article 
          $stockedImg = $imgRepo->findBy(['article'=> $article->getId()]);
          //dd($stockedImg);

          if($images){ 
            //vérification du nombre d'images en prenant compte celles dejà présentes
            $countImgByArticle = count($stockedImg) + count($images);

              //on limite le nombre d'images transférées par article
              if($countImgByArticle > $imgLimit){
                  $form->get('images')->addError(new FormError('Le nombre d\'images est trop important : la limite est de '.$imgLimit.' par article.'));
              }
          }

            if($form->isSubmitted() && $form->isValid()){

                //On garde la mise en place des sauts de ligne avec nl2br()
                $articleContent = nl2br($article->getContent());
                //On set le contenu modifié avec nl2br avant le persist et l'envoi en bdd
                $article->setContent($articleContent);

                //on verifie si il y a des nouvelles images
                $newImages = $form->get('images')->getData();

                if(!empty($newImages)){
                    foreach($newImages as $image){

                        $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                        $sluggedName = $slugger->slug($originalName);
                        $newName = $sluggedName.'-'.uniqid().'.'.$image->guessExtension();
     
                            try {
                                $image->move($this->getParameter('upload_image'), $newName); // ok
     
                            } catch(FileException $e) {
                                dd($e->getMessage());                    
                            }
     
                                $newImage = new Image();
                                $newImage->setSource($newName)
                                     ->setArticle($article); 
                                $article->addImage($newImage);
                                $manager->persist($newImage); 
                          
                            }      
                }  
                
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
            'title'=> 'Edition de l\'article ',
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

    //Commandes passées par l'utilisateur
    #[IsGranted("ROLE_USER")]
    #[Route('/profile/orders', name:'account_myOrders')]
    public function myOrders(OrderRepository $orderRepository){

        $user = $this->getUser();
        $orders = $orderRepository->findOrderSucceededByUser($user);

        return $this->render('/account/order/index.html.twig', [
            'title'=> 'Mes commandes',
            'user'=>$user,
            'orders'=>$orders
        ]);
    }

    //Liste des sessions dans lequel l'user est inscrit
    #[IsGranted("ROLE_USER")]
    #[Route('/profile/sessions', name:'account_mySessions')]
    public function mySessions(SessionRepository $sessionRepository){

        $user = $this->getUser();
        
        $sessions = $sessionRepository->findAllSessionsForUser($user);

        return $this->render('/account/mySessions.html.twig', [
            'title'=> 'Mes sessions',
            'user'=>$user,
            'sessions'=>$sessions
            
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/profile/session/delete/{id}/{username}', name:"account_session_delete")]   
    public function deleteSession(SessionRepository $sessionRepository, EntityManagerInterface $manager, $id, $username){
        $user = $this->getUser();
        $session = $sessionRepository->find($id);
 
        if($user == $username && $session){

            $session->removeUser($user);
            $manager->persist($session);
            $manager->flush();

            $this->addFlash('success','Vous êtes bien désinscrit de la session.');
            return $this->redirectToRoute('account_mySessions');
        }
        else {
            $this->addFlash('danger','Vous n\'êtes pas autorisé à accéder à cette page.');
            return $this->redirectToRoute('home');
        }

    }

    //Profil utilisateur public (articles de l'user, badge , drone favori, sessions de l'user)
    #[Route('/profile/{nickname}', name:'account_profile')]
     public function profile(User $user, ArticleRepository $articleRepo, SessionRepository $sessionRepository)
    {
        $nickname = $user->getNickname();
        $articles = $articleRepo->findBy(['author'=>$user, 'active'=>true ],['createdAt'=>'DESC']);
        $drone = $user->getDrone();
        $sessions = $sessionRepository->findSessionsForUser($user);

        return $this->render('/account/publicProfile.html.twig', [
            'title'=> 'Profil de '.$nickname.' ',
            'user'=>$user,
            'articles'=>$articles,
            'drone'=>$drone,
            'sessions'=> $sessions
        ]);
    }

}
