<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Alert;
use App\Entity\Image;
use App\Entity\Likes;
use App\Entity\Video;
use App\Entity\Article;
use App\Entity\Comment;
use App\Services\Media;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Entity\AlertComment;
use App\Services\Pagination;
use App\Form\AdminArticleType;
use App\Form\AlertArticleType;
use App\Repository\LikesRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AlertCommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;




class BlogController extends AbstractController
{
    // main page for blog (publications list)
    // page principale du blog (liste des articles)
    #[Route('/blog/{page<\d+>?1}', name: 'blog')]
    public function index(CategoryRepository $categoryRepo, Pagination $paginationService, int $page): Response
    {
        // $articles = $articleRepo->findAllArticlesByDate();
        

        // pagination service need: entity, current page, results limit, order. Property/Value are optionnals but help to hide moderated publications
        $paginationService->setEntityClass(Article::class)
                        ->setProperty('active')
                        ->setValue(true)
                        ->setPage($page)
                        ->setLimit(8)
                        ->setOrder('DESC');

        // to have all category results
        // pour récupérer les différents nom de catégories (pour le filtre par catégorie)
        $category = $categoryRepo->findAll();

        return $this->render('blog/index.html.twig', [
           'title' => 'Actualités ',
           'pagination' => $paginationService,
           'categories' => $category,
        ]);
    }

    // add a publication (possibility to add a video or/and images)
    // ajout d'un article (avec possibilité d'intégrer une vidéo et/ou des images)
    #[Route('/blog/add', name: 'article_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, EntityManagerInterface $manager, Media $mediaService): Response
    {
        // give authentified user data
        /** @var User $user */
        $user = $this->getUser();

        // new instance for Article(publication) and Video
        $article = new Article();
        $video = new Video();

        // check if the authentified user is an admin
        // vérification de l'accès de l'user authentifié
        $adminAccess = $this->isGranted('ROLE_ADMIN');

        // if he's an admin : show him a form with an option "A la Une" which show his publication on top of the homepage
        // si l'user est un admin: on lui présente le formulaire avec l'option permettant de mettre son article "à la une" de la page d'accueil
        if ($adminAccess) {
            $form = $this->createForm(AdminArticleType::class, $article);
        } else {
            //else the user will have a classic form without the "A la Une" option, his publication will be shown on classic section of home page
            //sinon l'user aura le formulaire classique, son article apparaîtra avec les autres dans les articles de la page d'accueil
            $form = $this->createForm(ArticleType::class, $article); 
        }
         
        $form->handleRequest($request);  
        
        // $images = $form['images']->getData();

        // check if the authentified user is an admin
        // vérification de l'accès de l'user authentifié
        $adminAccess = $this->isGranted('ROLE_ADMIN');

        // if he's an admin : show him a form with an option "A la Une" which show his publication on top of the homepage
        // si l'user est un admin: on lui présente le formulaire avec l'option permettant de mettre son article "à la une" de la page d'accueil
        if ($adminAccess) {
            $form = $this->createForm(AdminArticleType::class, $article);
        } else {
            // else the user will have a classic form without the "A la Une" option, his publication will be shown on classic section of home page
            // sinon l'user aura le formulaire classique, son article apparaîtra avec les autres dans les articles de la page d'accueil
            $form = $this->createForm(ArticleType::class, $article);
        }

        // take data from input before submit
        $form->handleRequest($request);

        // get "source" imput value for video
        // Récupération du champ source pour la vidéo
        $videoSource = $form->get('video')->get('source')->getData();

        // get "link" input to Youtube video
        // Récupération du lien Youtube
        $videoLink = $form->get('video')->get('link')->getData();

        // if both inputs are filled, add a form error
        // Si les 2 champs concernant l'ajout d'une vidéo sont remplis, on renvoie une erreur dans le formulaire
        if (isset($videoLink) && isset($videoSource)) {
            $form->get('video')->get('link')->addError(new FormError('Vous ne pouvez pas ajouter plusieurs vidéos à votre article : choisissez le téléversement OU l\'ajout de lien.'));
        }

        // allowed images amount
        // limite d'images autorisées
        $imgLimit = 8;
        $images = $form->get('images')->getData();

        if ($images) {
            // limit amount of images by publication
            // on limite le nombre d'images transférées par article
            if (count($images) > $imgLimit) {
                $form->get('images')->addError(new FormError('Le nombre d\'images est trop important : la limite est de '.$imgLimit.' par article.'));
            }
        }
        // if the form is submitted and valid
        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // get data from form
            // on récupère les données envoyées par le formulaire
            $article = $form->getData();

            // keep breaklines
            // on garde la mise en place des sauts de ligne avec nl2br()
            $articleContent = nl2br($article->getContent());

            // GESTION VIDEO
            // on teste si l'utilisateur a voulu mettre une vidéo : / try if user wanted to put a video :

            // Si il y a une vidéo, est-elle un fichier uploadé ?... / if it there a video, is-it an uploaded file ?
            // vérification de la présence de données dans le champ dédié à l'upload / if input source is filled
            if ($videoSource) {
           
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
                // convert URL set by user on a embed Youtube URL for direct playing video
                // On convertit l'URL Youtube fourni par l'user et on le convertit en URL "embed"
                $convertedURL = $video->convertYT($videoLink);
                $video->setSource($convertedURL);

                $video->setUser($user);
                $video->setIsUploaded(false);

                $manager->persist($video);

                $article->setVideo($video);
            }

            // GESTION IMAGE
            // si il y a des images, on les traite pour l'upload - if there are images, treatment for uploading them
            if ($images) {
                // for each gotten images
                // pour chaque image récupérée
                foreach ($images as $image) {
                
                    //path to upload directory
                    $path = $this->getParameter('upload_image');

                    //we go through media service to save image and get his new name    
                    $imgName = $mediaService->saveImageAndGetName($image, $path);

                    $newImage = new Image();
                    $newImage->setSource($imgName)
                         ->setArticle($article);
                    $article->addImage($newImage);

                    $manager->persist($newImage);
                }
            }
    
            $article->setContent($articleContent);
            $article->setAuthor($user);
            $manager->persist($article);

            $manager->flush();

            $this->addFlash('success', 'Article publié !');

            return $this->redirectToRoute('account_myprofile');
        }

        return $this->render('blog/article/add.html.twig', [
            'title' => 'Publier un article',
            'form' => $form->createView(),
            ]);
    }


    // Visualisation de l'article + autres articles du même auteur en excluant l'article actuel
    // (Utilisateur connecté uniquement : comparaison avec l'auteur pour ajout ou non d'une vue)
    // Show an article by his slug and show others publications by the same author, only for connected users + compare author with user to add or not a view
    #[Route('/blog/show/{slug}', name: 'article_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Article $article, ArticleRepository $articleRepo, EntityManagerInterface $manager, Request $request): Response
    {
        // if the publication is active or current user has an admin role
        if (true == $article->isActive() || $this->isGranted('ROLE_ADMIN')) {
            $author = $article->getAuthor();
            $title = $article->getTitle();
            $video = $article->getVideo();

             /** @var User $user */
            $user = $this->getUser();

            // gestion des signalements
            $alert = new Alert();
            $formAlertArticle = $this->createForm(AlertArticleType::class, $alert);
            $formAlertArticle->handleRequest($request);

            // gestion des commentaires
            $comment = new Comment();
            $formComment = $this->createForm(CommentType::class, $comment);
            $formComment->handleRequest($request);

            // si un signalement est soumis - if an alert is submitted
            if ($formAlertArticle->isSubmitted() && $formAlertArticle->isValid()) {
                // keep breaklines
                $nblrAlert = nl2br($alert->getDescription());
                $alert->setArticle($article)
                    ->setDescription($nblrAlert);
                $manager->persist($alert);
                $manager->flush();

                $this->addFlash('success', 'Votre signalement a bien été pris en compte.');

                return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
            }

            // si un commentaire est soumis - if a comment is sbmitted
            if ($formComment->isSubmitted() && $formComment->isValid()) {
                // On récupère le commentaire et on applique la méthode php nl2br() pour conserver les sauts de ligne //keep breaklines
                $nlbrContent = nl2br($comment->getContent());

                $comment->setAuthor($user)
                        ->setArticle($article)
                        ->setContent($nlbrContent);

                $manager->persist($comment);
                $manager->flush();

                $this->addFlash('success', 'Commentaire ajouté avec succès.');

                return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
            }

            // on ajoute une vue à l'article si le viewer n'est pas l'auteur de l'article - add a view if current user is not the author
            if ($this->getUser() != $author) {
                $article->setViews($article->getViews() + 1);
                $manager->persist($article);
                $manager->flush();
            }

            // paramètre pour obtenir les articles actifs - to get active publications
            $active = true;

            // articles associés à l'auteur - find other publications for the same author (without current publication for avoid duplication )
            $articles = $articleRepo->findOtherArticlesByAuthor($author->getId(), $article->getId(), $active);

            // si l'article est desactivé - if publication isn't active
        } elseif (false == $article->isActive()) {
            throw $this->createAccessDeniedException('Cet article n\'est pas accessible.');
        }

        return $this->render('blog/article/show.html.twig', [
            'article' => $article,
            'articles' => $articles,
            'video' => $video,
            'alert' => $alert,
            'title' => $title.' - '.$author,
            'formAlert' => $formAlertArticle->createView(),
            'form' => $formComment->createView(),
        ]);
    }

    // ajout d'un like / add a like
    #[Route('/blog/{id}/like', options: ['expose' => true], name: 'article_like')]
    public function like(Article $article, EntityManagerInterface $manager, LikesRepository $likesRepository): Response
    {
        // si on récupère un user connecté (connexion nécessaire pour l'accès à la visualisation)
        if ($this->getUser()) {
            /** @var User $user */
            $user = $this->getUser();

            // if user is not the author
            if ($user != $article->getAuthor()) {
                // check if it's already liked
                $isLiked = $likesRepository->getLikeByUserAndArticle($user, $article);

                // si c'est déjà liké et qu'on rappuie sur "like", on enlève le like
                // if it is already liked, remove associated like
                if ($isLiked) {
                    $manager->remove($isLiked);

                    // sinon on créé un nouveau Like qui va s'associer à l'utilisateur et à l'article
                    // else create a new instance of Like, associated with user and publication
                } else {
                    $like = new Likes();
                    $like->setUser($user)
                         ->setArticle($article);
                    $manager->persist($like);
                }

                $manager->flush();

                //success
                return new JsonResponse(['success'=> 200]);
            } else {
                return new JsonResponse(['error' => 'Vous ne pouvez pas liker vos articles.'], 400);
            }
            // si l'utilisateur tente de liker sans être connecté, on retourne une réponse en JSON
            // if user try to like without being connected, json response (in case of : not really possible to a classic access to a publication without authentification)
        } else {
            return new JsonResponse(['error' => 'Attention, vous devez être connecté pour pouvoir liker un article.'], 400);
        }

        return $this->render('blog/index.html.twig', [
            'title' => 'Actualités',
         ]);
    }

    // ajout d'un signalement sur un commentaire - add an alert on a comment
    #[Route('/blog/comment/{id}/alert', options: ['expose' => true], name: 'comment_alert')]
    #[IsGranted('ROLE_USER')]
    public function AlertComment(Comment $comment, AlertCommentRepository $alertCommentRepo, EntityManagerInterface $manager, Request $request): Response
    {
        // get json data
        // récupération des données json
        $data = json_decode($request->getContent(), true);

        /** @var User $user */
        $user = $this->getUser();

        // vérification du token - token verification
        if ($this->isCsrfTokenValid('alert'.$comment->getId(), $data['token'])) {
            // on vérifie que l'utilisateur n'a pas déjà signalé l'article - check if already alerted
            $isAlreadyAlerted = $alertCommentRepo->getAlertByUserAndComment($user, $comment);

            // si il y a déjà un signalement - if already alerted
            if ($isAlreadyAlerted) {
                // on l'enlève - we remove it
                $manager->remove($isAlreadyAlerted);
            } else {
                // sinon on crée un nouveau signalement - else we create a new instance of AlertComment
                $alertComment = new AlertComment();
                $alertComment->setUser($user)
                        ->setComment($comment);
                $manager->persist($alertComment);
            }

            $manager->flush();

            // success response
            return new JsonResponse(['success'=> 200]);
        } else {
            // si le token n'est pas valide - token invalid
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }
    }

    // Visualisation de l'article + autres articles du même auteur en excluant l'article actuel
    // (Utilisateur connecté uniquement : comparaison avec l'auteur pour ajout ou non d'une vue)
    // Show an article by his slug and show others publications by the same author, only for connected users + compare author with user to add or not a view
    #[Route('/blog/show/{slug}', name: 'article_show')]
    #[IsGranted('ROLE_USER')]
    public function show(Article $article, ArticleRepository $articleRepo, EntityManagerInterface $manager, Request $request)
    {
        // if the publication is active or current user has an admin role
        if (true == $article->isActive() || $this->isGranted('ROLE_ADMIN')) {
            $author = $article->getAuthor();
            $title = $article->getTitle();
            $video = $article->getVideo();

            // gestion des signalements
            $alert = new Alert();
            $formAlertArticle = $this->createForm(AlertArticleType::class, $alert);
            $formAlertArticle->handleRequest($request);

            // gestion des commentaires
            $comment = new Comment();
            $formComment = $this->createForm(CommentType::class, $comment);
            $formComment->handleRequest($request);

            // si un signalement est soumis - if an alert is submitted
            if ($formAlertArticle->isSubmitted() && $formAlertArticle->isValid()) {
                // keep breaklines
                $nblrAlert = nl2br($alert->getDescription());
                $alert->setArticle($article)
                    ->setDescription($nblrAlert);
                $manager->persist($alert);
                $manager->flush($alert);

                $this->addFlash('success', 'Votre signalement a bien été pris en compte.');

                return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
            }

            // si un commentaire est soumis - if a comment is sbmitted
            if ($formComment->isSubmitted() && $formComment->isValid()) {
                // On récupère le commentaire et on applique la méthode php nl2br() pour conserver les sauts de ligne //keep breaklines
                $nlbrContent = nl2br($comment->getContent());

                $comment->setAuthor($this->getUser())
                        ->setArticle($article)
                        ->setContent($nlbrContent);

                $manager->persist($comment);
                $manager->flush($comment);

                $this->addFlash('success', 'Commentaire ajouté avec succès.');

                return $this->redirectToRoute('article_show', ['slug' => $article->getSlug()]);
            }

            // on ajoute une vue à l'article si le viewer n'est pas l'auteur de l'article - add a view if current user is not the author
            if ($this->getUser() != $author) {
                $article->setViews($article->getViews() + 1);
                $manager->persist($article);
                $manager->flush();
            }

            // paramètre pour obtenir les articles actifs - to get active publications
            $active = true;

            // articles associés à l'auteur - find other publications for the same author (without current publication for avoid duplication )
            $articles = $articleRepo->findOtherArticlesByAuthor($author->getId(), $article, $active);

            // si l'article est desactivé - if publication isn't active
        } elseif (false == $article->isActive()) {
            throw $this->createAccessDeniedException('Cet article n\'est pas accessible.');
        }

        return $this->render('blog/article/show.html.twig', [
            'article' => $article,
            'articles' => $articles,
            'video' => $video,
            'alert' => $alert,
            'title' => $title.' - '.$author,
            'formAlert' => $formAlertArticle->createView(),
            'form' => $formComment->createView(),
        ]);
    }

    // ajout d'un like / add a like
    #[Route('/blog/{id}/like', options: ['expose' => true], name: 'article_like')]
    public function like(Article $article, EntityManagerInterface $manager, LikesRepository $likesRepository)
    {
        // si on récupère un user connecté (connexion nécessaire pour l'accès à la visualisation)
        if ($this->getUser()) {
            $user = $this->getUser();

            // if user is not the author
            if ($user != $article->getAuthor()) {
                // check if it's already liked
                $isLiked = $likesRepository->getLikeByUserAndArticle($user, $article);

                // si c'est déjà liké et qu'on rappuie sur "like", on enlève le like
                // if it is already liked, remove associated like
                if ($isLiked) {
                    $manager->remove($isLiked);

                    // sinon on créé un nouveau Like qui va s'associer à l'utilisateur et à l'article
                    // else create a new instance of Like, associated with user and publication
                } else {
                    $like = new Likes();
                    $like->setUser($user)
                         ->setArticle($article);
                    $manager->persist($like);
                }

                $manager->flush();

                return new JsonResponse(['success' => 200]);
            } else {
                return new JsonResponse(['error' => 'Vous ne pouvez pas liker vos articles.'], 400);
            }
            // si l'utilisateur tente de liker sans être connecté, on retourne une réponse en JSON
            // if user try to like without being connected, json response (in case of : not really possible to a classic access to a publication without authentification)
        } else {
            return new JsonResponse(['error' => 'Attention, vous devez être connecté pour pouvoir liker un article.'], 400);
        }

        return $this->render('blog/index.html.twig', [
            'title' => 'Actualités',
         ]);
    }

    // ajout d'un signalement sur un commentaire - add an alert on a comment
    #[Route('/blog/comment/{id}/alert', options: ['expose' => true], name: 'comment_alert')]
    #[IsGranted('ROLE_USER')]
    public function AlertComment(Comment $comment, AlertCommentRepository $alertCommentRepo, EntityManagerInterface $manager, Request $request)
    {
        // get json data
        // récupération des données json
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();

        // vérification du token - token verification
        if ($this->isCsrfTokenValid('alert'.$comment->getId(), $data['_token'])) {
            // on vérifie que l'utilisateur n'a pas déjà signalé l'article - check if already alerted
            $isAlreadyAlerted = $alertCommentRepo->getAlertByUserAndComment($user, $comment);

            // si il y a déjà un signalement - if already alerted
            if ($isAlreadyAlerted) {
                // on l'enlève - we remove it
                $manager->remove($isAlreadyAlerted);
            } else {
                // sinon on crée un nouveau signalement - else we create a new instance of AlertComment
                $alertComment = new AlertComment();
                $alertComment->setUser($user)
                        ->setComment($comment);
                $manager->persist($alertComment);
            }

            $manager->flush();

            // success response
            return new JsonResponse(['success' => 200]);
        } else {
            // si le token n'est pas valide - token invalid
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }
    }
}
