<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Drone;
use App\Entity\Image;
use App\Entity\Video;
use App\Entity\Article;
use App\Entity\Counter;
use App\Form\DroneType;
use App\Services\Media;
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    // Connexion
    // login
    #[Route('/login', name: 'account_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // on récupère l'erreur si il y en a une
        // recover error if there is it
        $error = $authenticationUtils->getLastAuthenticationError();

        // on récupère le dernier identifiant de connexion entré par l'utilisateur
        // last connexion identifier enter by user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('account/login.html.twig', [
            'title' => 'Connexion', 'last_username' => $lastUsername, 'error' => $error,
        ]);
    }

    // Déconnexion
    // logout
    #[Route('/logout', name: 'account_logout')]
    public function logout(): void
    {
    }

    // Inscription de l'utilisateur
    // user's registration
    #[Route('/register', name: 'account_register')]
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        // Si le formulaire est soumis réellement, et si il est valide
        // if the form is really submitted and if it's validated
        if ($form->isSubmitted() && $form->isValid()) {
            // Hash du mot de passe avant l'envoi dans la base de données
            // hash on password before sending on database
            $hash = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            // message flash dans un toast (popup de notification) pour informer l'utilisateur
            // flash message in a toast for inform user
            $this->addFlash('success', 'Votre compte a bien été créé !');

            // redirection
            return $this->redirectToRoute('account_login');
        }

        // affichage de la vue
        // link to the template for displaying view, create view for form
        return $this->render('account/register.html.twig', [
            'title' => 'Inscription',
            'form' => $form->createView(),
        ]);
    }

    // User's personnal profile (access to modifications for user's parameters, dashboard with profile )
    // Profil personnel de l'utilisateur (accès aux modifications paramètres user, vue globale de son profil)
    #[Route('/profile', name: 'account_myprofile')]
    #[IsGranted('ROLE_USER')]
    public function myProfile(ArticleRepository $articleRepo, SessionRepository $sessionRepository, OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();

        // amount of publications
        // nombre d'articles de l'user
        $articleCount = $articleRepo->countMyArticles($user);

        // amount of active flight sessions
        // nombre de sessions actives de l'user
        $sessionStatus = false;
        $sessionCount = $sessionRepository->countMySessions($user, $sessionStatus);

        // last three user's questions for an easy access to responses
        // 3 dernières questions de l'user pour accès rapides aux réponses
        $category = 'Question';
        $active = true;
        $myQuestions = $articleRepo->findMylastQuestions($user, $category, $active);

        // status of the last order which isn't canceled (4 is the status "cancelled")
        // status de la dernière commande en cours (pas annulée)
        // status = 4 correspond au statut des commandes annulées, donc à bannir du resultat retourné
        $status = 4;
        $lastOrderStatus = $orderRepository->findLastOrder($user, $status);

        return $this->render('account/myprofile.html.twig', [
            'title' => 'Mon compte ',
            'user' => $user,
            'articleCount' => $articleCount,
            'sessionCount' => $sessionCount,
            'questions' => $myQuestions,
            'lastOrderStatus' => $lastOrderStatus,
        ]);
    }

    // counter for lipo, esc and frame updated on homepage
    // Compteur de lipo, esc et frame mis à jour sur l'accueil
    #[Route('/profile/counter/{name}', name: 'account_add_to_counter')]
    #[IsGranted('ROLE_USER')]
    public function addToCounter(Counter $counter, EntityManagerInterface $manager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $count = $counter->getCount();
        $counter->setCount($count + 1)
                ->addUser($user);

        $manager->persist($counter);
        $manager->flush();
        return $this->redirectToRoute('account_myprofile');
    }

    // Profile edit
    // Edition des données personnelles du profil
    #[Route('/profile/edit', name: 'account_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(EntityManagerInterface $manager, Request $request, Media $mediaService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ajout d'avatar par l'user
            $avatar = $form->get('avatar')->getData();

            // on récupère le nom du fichier
            // file name
            if ($avatar) {
                $avatarPath = $this->getParameter('upload_avatar');
                //processing by media service and get unique name
                $avatarName = $mediaService->saveImageAndGetName($avatar, $avatarPath);

                $user->setAvatar($avatarName);
            }

            // ajout d'une bannière par l'user
            $banner = $form->get('banner')->getData();

            if ($banner) {
                $bannerPath = $this->getParameter('upload_banner');
                //processing by media service and get unique name
                $bannerName = $mediaService->saveImageAndGetName($banner,$bannerPath);

                $user->setBanner($bannerName);
            }

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour !');

            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/edit.html.twig', [
            'title' => 'Modifier le profil ',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // Modification of user's password
    // Modification du mot de passe de l'utilisateur
    #[Route('/profile/edit/password-update', name: 'account_pwd_edit')]
    #[isGranted('ROLE_USER')]
    public function passwordEdit(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $hasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $passwordUpdate = new PasswordUpdate();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // if actual password is not valid
            // le mot de passe actuel n'est pas bon
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getPassword())) {
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez entré n'est pas votre mot de passe actuel."));
            } else {
                // new password
                // récupération du nouveau mdp
                $newPassword = $passwordUpdate->getNewPassword();

                // hash of new password
                // hash du nouveau mdp
                $hash = $hasher->hashPassword($user, $newPassword);

                // set after hashing
                // on set le nouveau mdp
                $user->setPassword($hash);

                // send to database
                // ok donc on envoie à la bdd
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre nouveau mot de passe a bien été enregistré.');

                return $this->redirectToRoute('account_edit');
            }
        }

        return $this->render('account/passwordUpdate.html.twig', ['title' => ' Modification de votre mot de passe', 'form' => $form->createView()]);
    }

    // Drone edit (favourite config, public display)
    // Edition de mon drone (ma configuration favorite, visible au public)
    #[Route('/profile/edit/favorite', name: 'account_drone_edit')]
    #[IsGranted('ROLE_USER')]
    public function droneEdit(EntityManagerInterface $manager, Request $request, Media $mediaService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        // on tente de récupérer le drone associé à l'user
        $drone = $user->getDrone();

        $form = $this->createForm(DroneType::class, $drone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // if there's no drone
            // si il n'y a pas de drone enregistré pour l'user
            if (null == $drone) {
                // create a drone
                // création d'un drone
                $drone = new Drone();

                // on récupère les données entrées par l'user
                $frame = $form->get('frame')->getData();
                $motors = $form->get('motors')->getData();
                $fc = $form->get('fc')->getData();
                $esc = $form->get('esc')->getData();
                $cam = $form->get('cam')->getData();
                $reception = $form->get('reception')->getData();
                $lipo = $form->get('lipoCells')->getData();

                // set des données sur le nouvel objet Drone et association avec l'user actuel
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

            // add an image for the drone ( not required )
            // ajout d'image pour le drone (pas obligatoire)
            $image = $form->get('image')->getData();

            if ($image) {
                
                $dronePath = $this->getParameter('upload_drone');
                $imgName = $mediaService->saveImageAndGetName($image, $dronePath);

                $drone->setImage($imgName);
            }

            $manager->persist($drone);
            // envoi en bdd du nouveau drone / de la modification du drone
            $manager->flush();
            $this->addFlash('success', 'Votre drone a été mis à jour !');
            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('account/droneEdit.html.twig', [
            'title' => 'Ajouter ou modifier mon drone ',
            'user' => $user,
            'drone' => $drone,
            'form' => $form->createView(),
        ]);
    }

    // GESTION DES ARTICLES PROPRES A L'UTILISATEUR

    // show personnal publication in account section
    // Visualisation des articles de l'utilisateur connecté sur sa page profil
    #[Route('/profile/articles/{page<\d+>?1}', name: 'account_articles')]
    #[IsGranted('ROLE_USER')]
    public function myArticles(Pagination $paginationService, int $page): Response
    {
        $user = $this->getUser();

        // get user's publication by date of creation
        // Récupération des articles de l'user connecté par date de publication la plus récente
        // $articles = $articleRepository->findBy(['author'=> $user], ['createdAt'=>'DESC']);
        $paginationService->setEntityClass(Article::class)
                         ->setPage($page)
                         ->setLimit(5)
                         ->setOrder('DESC')
                         ->setProperty('author')
                         ->setValue($user)
        ;

        return $this->render('account/article/index.html.twig', [
            'title' => 'Mes articles ',
            'user' => $user,
            'pagination' => $paginationService,
        ]);
    }

    // Edit a publication
    // Edition d'un article
    #[Route('/profile/articles/edit/{id}', name: 'account_article_edit')]
    #[IsGranted('ROLE_USER')]
    public function editArticle(Article $article, ImageRepository $imgRepo, EntityManagerInterface $manager, Request $request, Media $mediaService): Response
    {
        $user = $this->getUser();

        if ($user == $article->getAuthor()) {
            // check if user has an admin role
            // vérification de l'accès de l'user connecté
            $adminAccess = $this->isGranted('ROLE_ADMIN');

            // if he's an admin : form with option to display on top of homepage : "A la Une"
            // si l'user est un admin: on lui présente le formulaire avec l'option permettant de mettre son article "à la une" de la page d'accueil
            if ($adminAccess) {
                $form = $this->createForm(AdminArticleType::class, $article);
            } else {
                // if he has a user role, he will have a classic form, his publication will be showing with other, on blog section
                // sinon l'user aura le formulaire classique, son article apparaîtra avec les autres dans la sections blog (actualités)
                $form = $this->createForm(ArticleType::class, $article);
            }

            $form->handleRequest($request);

            // Récupération du champ source pour la vidéo
            $videoSource = $form->get('video')->get('source')->getData();

            // Récupération du lien Youtube ou Vimeo
            $videoLink = $form->get('video')->get('link')->getData();

            // if two inputs are filled, we send an error
            // Si les 2 champs concernant l'ajout d'une vidéo sont remplis, on renvoie une erreur
            if (isset($videoLink) && isset($videoSource)) {
                $form->get('video')->get('link')->addError(new FormError('Vous ne pouvez pas ajouter plusieurs vidéos à votre article : choisissez le téléversement OU l\'ajout de lien.'));
            }

            if ($videoSource) {

                $video = new Video();
           
                $title = $form->get('video')->get('title')->getData();

                //we go through mediaService for video processing and get datas from it
                $videoDatas = $mediaService->VideoProcessingAndReturnDatas($videoSource);

                //set datas to video
                $video->setThumbnail($videoDatas['thumbName'])
                        ->setDuration($videoDatas['duration'])
                        ->setSource($videoDatas['videoNewName']);

                // si il y a un titre - if there's a title
                if ($title) {
                    $video->setTitle($title);
                }
                $video->setUser($user);
                $video->setIsUploaded(true);

                $manager->persist($video);

                // association de la vidéo avec l'article - set video to the article data
                $article->setVideo($video);

                // ...Ou est-ce que la vidéo est un lien vers Youtube ? - ... Or the video is a Youtube integration ?
            } elseif ($videoLink) {
                $video = new Video();
           
                // convert URL set by user on a embed Youtube URL for direct playing video
                // On convertit l'URL Youtube fourni par l'user et on le convertit en URL "embed"
                $convertedURL = $video->convertYT($videoLink);
                $video->setSource($convertedURL);

                $video->setUser($user);
                $video->setIsUploaded(false);

                $manager->persist($video);

                $article->setVideo($video);
            }

            // limite d'images imposée par article
            $imgLimit = 8;

            // images envoyées lors du formulaire d'edition
            $images = $form->get('images')->getData();

            // image already on the publication
            // images déjà présentes sur l'article
            $stockedImg = $imgRepo->findBy(['article' => $article->getId()]);

            if ($images) {
                // check the amount of images with those which are already on the publication
                // vérification du nombre d'images en prenant compte celles dejà présentes
                $countImgByArticle = count($stockedImg) + count($images);

                // limit the amount of images by publication
                // on limite le nombre d'images transférées par article
                if ($countImgByArticle > $imgLimit) {
                    $form->get('images')->addError(new FormError('Le nombre d\'images est trop important : la limite est de '.$imgLimit.' par article.'));
                }
            }

            if ($form->isSubmitted() && $form->isValid()) {
                // On garde la mise en place des sauts de ligne avec nl2br()
                // Keep lineBreak
                $articleContent = nl2br($article->getContent());

                // On set le contenu modifié avec nl2br avant le persist et l'envoi en bdd
                $article->setContent($articleContent);

                // on verifie si il y a des nouvelles images
                $newImages = $form->get('images')->getData();

                // boucle sur chaque nouvelle image
                // loop on each new image
                if (!empty($newImages)) {
                    foreach ($newImages as $image) {

                        $imgPath = $this->getParameter('upload_image');
                        //we go through media service to save image and get his new name  
                        $imgName = $mediaService->saveImageAndGetName($image, $imgPath);

                        $newImage = new Image();
                        $newImage->setSource($imgName)
                                ->setArticle($article);
                        $article->addImage($newImage);
                        $manager->persist($newImage);
                    }
                }

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

                $this->addFlash('success', 'Votre article a bien été modifié !');

                return $this->redirectToRoute('account_articles');
            }
        } else {
            return $this->redirectToRoute('home');
        }

        return $this->render('account/article/edit.html.twig', [
            'title' => 'Edition de l\'article ',
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    //delete an image on publication edition
    //supprime une image dans l'édition d'un article
    #[isGranted('ROLE_USER')]
    #[Route('/profile/articles/image/delete/{id}', name:'account_article_edit_delete_img')]
    public function deleteImageFromMyArticle(Request $request, EntityManagerInterface $manager, ImageRepository $imageRepository, int $id): Response
    {
        //get data and decode from json format
        $data = json_decode($request->getContent(), true);
        $image = $imageRepository->findOneBy(['id'=>$id]);

        // vérification du token - token verification
        if ($this->isCsrfTokenValid('delete'.$image->getId() , $data['token'])) {

            //remove image from directory
            unlink($this->getParameter('upload_image').$image->getSource());
            //remove instance of image
            $manager->remove($image);
            //send to database
            $manager->flush();
            //return success response to continue action on edit page
            return new JsonResponse(['success' => 200]);

        } else {
            return new JsonResponse(['error'=>'Erreur de token'], 400);
        }   
        
    }

    // Delete a publication
    // Suppression d'un article
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/articles/delete/{id}', name: 'account_article_delete')]
    public function deleteArticle(Article $article, EntityManagerInterface $manager, Request $request): Response
    {
        $token = $request->request->get('token');

        if ($this->isCsrfTokenValid('delete'.$article->getId(), $token)) {
            $user = $this->getUser();

            if ($user == $article->getAuthor()) {
                $manager->remove($article);
                $manager->flush();

                $this->addFlash('success', 'Votre article a bien été supprimé !');

                return $this->redirectToRoute('account_articles');
            } else {
                return $this->redirectToRoute('home');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }


    // user's orders
    // Commandes passées par l'utilisateur
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/orders', name: 'account_myOrders')]
    public function myOrders(OrderRepository $orderRepository): Response
    {
        $user = $this->getUser();
        $status = 'succeeded';
        $orders = $orderRepository->findOrderSucceededByUser($user, $status);

        return $this->render('/account/order/index.html.twig', [
            'title' => 'Mes commandes',
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    // flight sessions list where user is registered
    // Liste des sessions dans lequel l'user est inscrit
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/sessions', name: 'account_mySessions')]
    public function mySessions(SessionRepository $sessionRepository): Response
    {
        $user = $this->getUser();

        // $sessionStatus = false to remove past flight sessions in results
        // $sessionStatus = false pour enlever les sessions passées dans les résultats
        $sessionStatus = false;
        $sessions = $sessionRepository->findAllSessionsForUser($user, $sessionStatus);

        return $this->render('/account/mySessions.html.twig', [
            'title' => 'Mes sessions',
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    // se désinscrire d'une session
    // unsubscribe the user from a flight session
    #[IsGranted('ROLE_USER')]
    #[Route('/profile/session/delete/{id}/{username}', name: 'account_session_delete')]
    public function deleteSession(SessionRepository $sessionRepository, EntityManagerInterface $manager, int $id, string $username, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $token = $request->request->get('token');

        $session = $sessionRepository->find($id);

        if ($this->isCsrfTokenValid('unsub'.$session->getId(), $token)) {
            // si l'utilisateur connecté est le meme que le pseudo et que la session correspond bien a celle sur laquelle on se désinscrit
            // if the authenticated user is the same as the nickname and if flight session match between id and the id recovered from routing
            if ($user == $username && $session) {
                $session->removeUser($user);
                $manager->persist($session);

                // si il n'y a plus d'utilisateur sur la session, on la supprime
                // if flight session doesn't have users anymore, we delete it
                if (0 == count($session->getUsers())) {
                    $manager->remove($session);
                }

                $manager->flush();

                $this->addFlash('success', 'Vous êtes bien désinscrit de la session.');

                return $this->redirectToRoute('account_mySessions');
            } else {
                $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à cette page.');

                return $this->redirectToRoute('home');
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    // Profil utilisateur public (articles de l'user, badge , drone favori, sessions de l'user)
    #[Route('/profile/{nickname}', name: 'account_profile')]
    public function profile(User $user, ArticleRepository $articleRepo, SessionRepository $sessionRepository): Response
    {
        $nickname = $user->getNickname();
        $articles = $articleRepo->findBy(['author' => $user, 'active' => true], ['createdAt' => 'DESC']);
        $drone = $user->getDrone();
        $sessions = $sessionRepository->findSessionsForUser($user);

        return $this->render('/account/publicProfile.html.twig', [
            'title' => 'Profil de '.$nickname.' ',
            'user' => $user,
            'articles' => $articles,
            'drone' => $drone,
            'sessions' => $sessions,
        ]);
    }
}
